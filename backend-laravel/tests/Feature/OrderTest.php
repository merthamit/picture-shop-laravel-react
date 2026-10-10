<?php

namespace Tests\Feature;

use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\Picture;
use App\Models\User;
use App\Notifications\OrderPlaced;
use App\Notifications\PictureSold;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_placing_an_order_queues_processing_and_uses_database_prices(): void
    {
        Queue::fake();
        $buyer = User::factory()->create();
        $picture = Picture::factory()->create(['price' => 25]);

        $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/store/order', ['pictures' => [['id' => $picture->id, 'price' => 1]]])
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'user_id' => $buyer->id,
            'picture_id' => $picture->id,
            'total' => 25,
            'status' => Order::STATUS_PENDING,
        ]);
        Queue::assertPushedOn('orders', ProcessOrder::class);
    }

    public function test_own_and_already_bought_pictures_are_skipped(): void
    {
        Queue::fake();
        $buyer = User::factory()->create();
        $own = Picture::factory()->for($buyer)->create();
        $bought = Picture::factory()->create();
        Order::factory()->for($buyer)->for($bought)->create();

        $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/store/order', ['pictures' => [['id' => $own->id], ['id' => $bought->id]]])
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

    public function test_payment_reports_a_missing_stripe_key(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/order/pay', ['cartItems' => []])
            ->assertStatus(503)
            ->assertJsonStructure(['error']);
    }
}
