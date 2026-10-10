<?php

namespace Tests\Feature;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * Uses the real Redis server (database 15, see phpunit.xml).
 */
class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            Redis::connection()->ping();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Redis is not available.');
        }
        Redis::connection()->flushdb();
    }

    public function test_the_cart_session_lives_in_redis(): void
    {
        $user = User::factory()->create();
        $picture = Picture::factory()->create(['price' => 18]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart', ['picture_id' => $picture->id])
            ->assertOk()
            ->assertJsonPath('cartItems.0.price', 18);

        $this->assertTrue((bool) Redis::exists("cart:{$user->id}"));
        $this->assertGreaterThan(0, Redis::ttl("cart:{$user->id}"));

        $this->getJson('/api/cart')->assertJsonCount(1, 'cartItems');
        $this->deleteJson("/api/cart/{$picture->id}")->assertJsonCount(0, 'cartItems');
    }

    public function test_hidden_pictures_cannot_be_added(): void
    {
        $picture = Picture::factory()->hidden()->create();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/cart', ['picture_id' => $picture->id])
            ->assertNotFound();
    }
}
