<?php

namespace App\Services;

use Stripe\PaymentIntent;
use Stripe\StripeClient;

/**
 * Thin wrapper around the Stripe API so orders can be checked against the
 * real PaymentIntent (and so tests can swap it for a fake).
 */
class StripePayments
{
    public function configured(): bool
    {
        return (bool) config('services.stripe.secret');
    }

    public function retrieve(string $paymentIntentId): PaymentIntent
    {
        return (new StripeClient(config('services.stripe.secret')))
            ->paymentIntents->retrieve($paymentIntentId);
    }
}
