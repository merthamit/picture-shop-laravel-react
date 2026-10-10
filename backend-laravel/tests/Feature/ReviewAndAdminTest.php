<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Picture;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviews_always_belong_to_the_authenticated_user(): void
    {
        $order = Order::factory()->create();
        $victim = User::factory()->create();

        $this->actingAs($order->user, 'sanctum')
            ->postJson('/api/store/review', [
                'picture_id' => $order->picture_id,
                'user_id' => $victim->id, // ignored
                'rating' => 5,
                'comment' => 'Great',
            ])
            ->assertOk();

        $this->assertDatabaseHas('reviews', ['user_id' => $order->user_id, 'picture_id' => $order->picture_id, 'approved' => 0]);
        $this->assertDatabaseMissing('reviews', ['user_id' => $victim->id]);
    }

    public function test_users_cannot_delete_someone_elses_review(): void
    {
        $order = Order::factory()->create();
        Review::create(['user_id' => $order->user_id, 'picture_id' => $order->picture_id, 'rating' => 5, 'comment' => 'Mine']);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/delete/review', ['picture_id' => $order->picture_id, 'user_id' => $order->user_id])
            ->assertJsonStructure(['error']);

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_only_buyers_can_review(): void
    {
        $picture = Picture::factory()->create();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/store/review', ['picture_id' => $picture->id, 'rating' => 4, 'comment' => 'Nice'])
            ->assertJsonPath('error', __('messages.review_buyers_only'));
    }

    public function test_admin_pages_require_an_admin(): void
    {
        foreach (['/admin/dashboard', '/admin/users', '/admin/orders', '/admin/pictures', '/admin/reviews', '/admin/categories'] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
    }

    public function test_admins_can_manage_the_marketplace(): void
    {
        $admin = Admin::factory()->create();
        $order = Order::factory()->create();
        $review = Review::create(['user_id' => $order->user_id, 'picture_id' => $order->picture_id, 'rating' => 4, 'comment' => 'Ok']);

        $this->actingAs($admin, 'admin');
        foreach (['/admin/dashboard', '/admin/users', '/admin/orders', '/admin/pictures', '/admin/reviews', '/admin/categories'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->from('/admin/reviews')->get(route('admin.reviews.edit', ['review' => $review, 'status' => 1]))
            ->assertRedirect('/admin/reviews');
        $this->assertSame(1, (int) $review->fresh()->approved);
    }
}
