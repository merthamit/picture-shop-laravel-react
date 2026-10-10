<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\Picture;
use App\Services\CartService;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class OrderController extends Controller
{
    public function store(Request $request, CartService $cart)
    {
        $user = $request->user();
        $pictures = $this->purchasablePictures($request->pictures, $user);

        if ($pictures->isEmpty()) {
            return response()->json(['errors' => ['pictures' => [__('messages.nothing_to_order')]]], 422);
        }

        $total = $this->calculateOrderTotal($pictures, $savingForDatabase = true);

        // Persist the orders quickly, then hand the heavy lifting
        // (status update, notifications) to the RabbitMQ worker.
        $orderIds = DB::transaction(function () use ($pictures, $user, $total) {
            return $pictures->map(fn ($picture) => Order::create([
                'user_id' => $user->id,
                'picture_id' => $picture->id,
                'total' => $total,
                'status' => Order::STATUS_PENDING,
            ])->id)->all();
        });

        ProcessOrder::dispatch($orderIds)->afterCommit();
        $cart->clear($user->id);

        return response()->json([
            'user' => UserResource::make($user),
        ]);
    }
    public function payByStripe(Request $request)
    {
        if (!config('services.stripe.secret')) {
            return response()->json(['error' => __('messages.payment_not_configured')], 503);
        }

        $pictures = $this->purchasablePictures($request->cartItems, $request->user());
        if ($pictures->isEmpty()) {
            return response()->json(['error' => __('messages.nothing_to_order')], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        try {

            // Create a PaymentIntent with amount and currency
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $this->calculateOrderTotal($pictures, $savingForDatabase = false),
                'currency' => 'usd',
                'description' => 'Picture Shop order',
                'metadata' => [
                    'user_id' => $request->user()->id,
                    'picture_ids' => $pictures->pluck('id')->implode(','),
                ],
                'setup_future_usage' => 'on_session',
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];
            return response()->json($output);

        } catch (ApiErrorException | ErrorException $e) {
            report($e);
            return response()->json(['error' => __('messages.payment_failed')], 502);
        }
    }

    /**
     * Load the requested pictures from the database so prices
     * can never be tampered with on the client.
     */
    private function resolvePictures($items)
    {
        $ids = collect($items)->pluck('id')->filter()->unique();

        return Picture::whereIn('id', $ids)->where('status', 1)->get();
    }

    /**
     * Pictures from the cart the user can actually buy: never charge for
     * their own pictures or ones they already purchased.
     */
    private function purchasablePictures($items, $user)
    {
        return $this->resolvePictures($items)
            ->reject(fn ($picture) => $picture->isOwnedBy($user) || $picture->isPurchasedBy($user))
            ->values();
    }

    public function calculateOrderTotal($pictures, $savingForDatabase)
    {
        $total = $pictures->sum('price');
        return $savingForDatabase ? $total : $total * 100;
    }
}
