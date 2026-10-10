<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\OrderPlaced;
use App\Notifications\PictureSold;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Consumed from the RabbitMQ "orders" queue. Completes the pending orders
 * and fans out notifications (on the "notifications" queue) so the HTTP
 * request that created the orders never blocks on this work.
 */
class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 30, 60];

    /**
     * @param  array<int>  $orderIds
     */
    public function __construct(public array $orderIds)
    {
        $this->onQueue('orders');
    }

    public function handle(): void
    {
        $orders = Order::with(['user', 'picture.user'])
            ->whereIn('id', $this->orderIds)
            ->where('status', Order::STATUS_PENDING)
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        Order::whereIn('id', $orders->pluck('id'))->update(['status' => Order::STATUS_COMPLETED]);

        $buyer = $orders->first()->user;
        $buyer->notify(new OrderPlaced($orders));

        foreach ($orders as $order) {
            $seller = $order->picture->user;
            if ($seller && $seller->id !== $buyer->id) {
                $seller->notify(new PictureSold($order));
            }
        }

        Log::info('Orders processed', ['order_ids' => $orders->pluck('id')->all()]);
    }

    public function failed(\Throwable $e): void
    {
        Order::whereIn('id', $this->orderIds)->update(['status' => Order::STATUS_FAILED]);
        Log::error('Order processing failed', ['order_ids' => $this->orderIds, 'error' => $e->getMessage()]);
    }
}
