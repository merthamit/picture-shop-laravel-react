<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use ErrorException;
use Illuminate\Http\Request;
use Stripe\Stripe;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = [];
        $user = User::findOrFail($request->user_id);

        foreach ($request->pictures as $picture) {
            $data['user_id'] = $user->id;
            $data['picture_id'] = $picture['id'];
            $data['total'] = $this->calculateOrderTotal($request->pictures, $savingForDatabase = true);

            Order::create($data);

        }
        return response()->json([
            'user' => UserResource::make($request->user()),
        ]);
    }
    public function payByStripe(Request $request)
    {
        Stripe::setApiKey('sk_test_51PtWxg06PAiJgxypKOOuuQufGET6DQvBTFDl0gqN1owSUgIeGd27BjHJyJygazpgFW63l7HAZEYuYQ7eyRMTGFzI00hMU9xPZ4');
        try {

            // Create a PaymentIntent with amount and currency
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $this->calculateOrderTotal($request->cartItems, $savingForDatabase = false),
                'currency' => 'usd',
                'description' => 'React Store',
                'setup_future_usage' => 'on_session',
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];
            return response()->json($output);

        } catch (ErrorException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function calculateOrderTotal($pictures, $savingForDatabase)
    {
        $total = 0;
        foreach ($pictures as $picture) {
            $total += $picture['price'];
        }
        return $savingForDatabase ? $total : $total * 100;
    }
}
