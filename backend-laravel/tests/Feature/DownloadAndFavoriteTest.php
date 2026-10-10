<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadAndFavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_download(): void
    {
        $picture = Picture::factory()->create();

        $this->getJson("/api/download/picture/{$picture->id}")->assertUnauthorized();
    }

    public function test_users_who_did_not_buy_cannot_download(): void
    {
        $picture = Picture::factory()->create();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson("/api/download/picture/{$picture->id}")
            ->assertForbidden();
    }

    public function test_buyers_and_owners_get_a_download_link(): void
    {
        $picture = Picture::factory()->create(['title' => 'Sunset']);
        $order = Order::factory()->for($picture)->create();

        foreach ([$order->user, $picture->user] as $user) {
            $this->actingAs($user, 'sanctum')
                ->getJson("/api/download/picture/{$picture->id}")
                ->assertOk()
                ->assertJsonPath('filename', 'Sunset.jpg')
                ->assertJsonStructure(['url']);
        }
    }

    public function test_favorites_can_be_toggled_and_listed(): void
    {
        $user = User::factory()->create();
        $picture = Picture::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/favorites/{$picture->id}")
            ->assertOk()
            ->assertJsonPath('favorited', true)
            ->assertJsonPath('favorite_ids', [$picture->id]);

        $this->getJson('/api/favorites')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $picture->id);
        $this->getJson('/api/user')->assertJsonPath('user.favorite_ids', [$picture->id]);

        $this->postJson("/api/favorites/{$picture->id}")->assertJsonPath('favorited', false);
        $this->assertDatabaseCount('favorites', 0);
    }
}
