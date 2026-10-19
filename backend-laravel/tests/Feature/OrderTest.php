<?php

namespace Tests\Feature;

use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\Picture;
use App\Models\User;
use App\Notifications\OrderPlaced;
use App\Notifications\PictureSold;
use App\Services\StripePayments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Stripe\PaymentIntent;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Swap the Stripe API for a PaymentIntent we control.
     */
    private function fakeIntent(User $user, array $pictures, array $overrides = []): void
    {
        config(['services.stripe.secret' => 'sk_test_fake']);
        $intent = PaymentIntent::constructFrom(array_merge([
            'id' => 'pi_test_123',
            'status' => 'succeeded',
            'amount' => collect($pictures)->sum('price') * 100,
            'metadata' => [
                'user_id' => (string) $user->id,
                'picture_ids' => collect($pictures)->pluck('id')->implode(','),
            ],
        ], $overrides));

        $this->app->instance(StripePayments::class, new class($intent) extends StripePayments {
            public function __construct(private PaymentIntent $intent)
            {
            }

            public function retrieve(string $paymentIntentId): PaymentIntent
            {
                return $this->intent;
            }
        });
    }

    public function test_placing_an_order_queues_processing_and_stores_each_picture_price(): void
    {
        Queue::fake();
        $buyer = User::factory()->create();
        $first = Picture::factory()->create(['price' => 25]);
        $second = Picture::factory()->create(['price' => 10]);
        $this->fakeIntent($buyer, [$first, $second]);

        $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/store/order', ['payment_intent_id' => 'pi_test_123'])
            ->assertOk();

        $this->assertDatabaseHas('orders', ['picture_id' => $first->id, 'total' => 25, 'status' => Order::STATUS_PENDING, 'payment_intent_id' => 'pi_test_123']);
        $this->assertDatabaseHas('orders', ['picture_id' => $second->id, 'total' => 10]);
        Queue::assertPushedOn('orders', ProcessOrder::class);
    }

    public function test_an_order_cannot_be_placed_without_a_payment(): void
    {
        Queue::fake();
        $buyer = User::factory()->create();
        $picture = Picture::factory()->create();

        $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/store/order', ['pictures' => [['id' => $picture->id]]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('orders', 0);
        Queue::assertNothingPushed();
    }

    public function test_unpaid_or_mismatched_payments_are_rejected(): void
    {
        Queue::fake();
        $buyer = User::factory()->create();
        $picture = Picture::factory()->create(['price' => 25]);
        $cases = [
            ['status' => 'requires_payment_method'],
            ['amount' => 100],
            ['metadata' => ['user_id' => (string) User::factory()->create()->id, 'picture_ids' => (string) $picture->id]],
        ];

        foreach ($cases as $overrides) {
            $this->fakeIntent($buyer, [$picture], $overrides);
            $this->actingAs($buyer, 'sanctum')
                ->postJson('/api/store/order', ['payment_intent_id' => 'pi_test_123'])
                ->assertUnprocessable();
        }

        $this->assertDatabaseCount('orders', 0);
        Queue::assertNothingPushed();
    }

    public function test_a_payment_can_only_be_used_once(): void
    {
        Queue::fake();
        $buyer = User::factory()->create();
        $picture = Picture::factory()->create();
        $this->fakeIntent($buyer, [$picture]);

        $this->actingAs($buyer, 'sanctum')->postJson('/api/store/order', ['payment_intent_id' => 'pi_test_123'])->assertOk();
        $this->actingAs($buyer, 'sanctum')->postJson('/api/store/order', ['payment_intent_id' => 'pi_test_123'])->assertUnprocessable();

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_own_and_already_bought_pictures_are_skipped(): void
    {
        Queue::fake();
        $buyer = User::factory()->create();
        $own = Picture::factory()->for($buyer)->create();
        $bought = Picture::factory()->create();
        Order::factory()->for($buyer)->for($bought)->create();
        $this->fakeIntent($buyer, [$own, $bought]);

        $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/store/order', ['payment_intent_id' => 'pi_test_123'])
            ->assertUnprocessable();

        Queue::assertNothingPushed();
    }

    public function test_unverified_users_cannot_order(): void
    {
        $this->actingAs(User::factory()->unverified()->create(), 'sanctum')
            ->postJson('/api/store/order', ['pictures' => []])
            ->assertForbidden();
    }

    public function test_the_job_completes_orders_and_notifies_buyer_and_seller(): void
    {
        Notification::fake();
        $buyer = User::factory()->create();
        $picture = Picture::factory()->create();
        $order = Order::factory()->for($buyer)->for($picture)->create(['status' => Order::STATUS_PENDING]);

        (new ProcessOrder([$order->id]))->handle();

        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        Notification::assertSentTo($buyer, OrderPlaced::class);
        Notification::assertSentTo($picture->user, PictureSold::class);
    }

    public function test_payment_never_charges_for_pictures_already_owned(): void
    {
        $buyer = User::factory()->create();
        $bought = Picture::factory()->create(['price' => 30]);
        $new = Picture::factory()->create(['price' => 12]);
        Order::factory()->for($buyer)->for($bought)->create();

        $total = (new \App\Http\Controllers\Api\OrderController)->calculateOrderTotal(
            (fn () => $this->purchasablePictures([['id' => $bought->id], ['id' => $new->id]], $buyer))
                ->call(new \App\Http\Controllers\Api\OrderController),
            false
        );

        $this->assertSame(1200, $total);
    }

    public function test_payment_reports_a_missing_stripe_key(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/order/pay', ['cartItems' => []])
            ->assertStatus(503)
            ->assertJsonStructure(['error']);
    }
}
