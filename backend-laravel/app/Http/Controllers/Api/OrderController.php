<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\Picture;
use App\Services\CartService;
use App\Services\StripePayments;
use ErrorException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class OrderController extends Controller
{
    /**
     * Turns a successful Stripe payment into orders. The pictures, the
     * amount and the buyer all come from the PaymentIntent on Stripe's
     * side, so an order can never be created without paying for it.
     */
    public function store(Request $request, CartService $cart, StripePayments $stripe)
    {
        $request->validate(['payment_intent_id' => 'required|string|max:255']);
        $user = $request->user();

        if (!$stripe->configured()) {
            return response()->json(['errors' => ['payment' => [__('messages.payment_not_configured')]]], 503);
        }

        $intentId = $request->payment_intent_id;
        if (Order::where('payment_intent_id', $intentId)->exists()) {
            return $this->paymentError('messages.payment_already_used');
        }

        try {
            $intent = $stripe->retrieve($intentId);
        } catch (ApiErrorException $e) {
            report($e);
            return $this->paymentError('messages.payment_invalid');
        }

        $paidIds = collect(explode(',', (string) ($intent->metadata['picture_ids'] ?? '')))
            ->filter()->map(fn ($id) => (int) $id)->sort()->values();
        $pictures = $this->purchasablePictures($paidIds->map(fn ($id) => ['id' => $id]), $user);

        $valid = $intent->status === 'succeeded'
            && (string) ($intent->metadata['user_id'] ?? '') === (string) $user->id
            && $pictures->isNotEmpty()
            && $pictures->pluck('id')->sort()->values()->all() === $paidIds->all()
            && (int) $intent->amount === (int) $this->calculateOrderTotal($pictures, $savingForDatabase = false);

        if (!$valid) {
            return $this->paymentError('messages.payment_invalid');
        }

        // Persist the orders quickly, then hand the heavy lifting
        // (status update, notifications) to the RabbitMQ worker.
        try {
            $orderIds = DB::transaction(function () use ($pictures, $user, $intentId) {
                return $pictures->map(fn ($picture) => Order::create([
                    'user_id' => $user->id,
                    'picture_id' => $picture->id,
                    'total' => $picture->price,
                    'status' => Order::STATUS_PENDING,
                    'payment_intent_id' => $intentId,
                ])->id)->all();
            });
        } catch (UniqueConstraintViolationException) {
            // The same payment was submitted twice at the same time
            return $this->paymentError('messages.payment_already_used');
        }

        ProcessOrder::dispatch($orderIds)->afterCommit();
        $cart->clear($user->id);

        return response()->json([
            'user' => UserResource::make($user),
        ]);
    }

    private function paymentError(string $key)
    {
        return response()->json(['errors' => ['payment' => [__($key)]]], 422);
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
