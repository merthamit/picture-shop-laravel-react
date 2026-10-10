<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PictureManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_verified_user_can_upload_a_picture(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/store/picture', [
                'title' => 'Sunset',
                'price' => 12,
                'category_id' => $category->id,
                'user_id' => 999, // ignored: the picture belongs to the authenticated user
                'file' => UploadedFile::fake()->image('sunset.jpg', 800, 600),
            ])
            ->assertOk();

        $picture = Picture::firstOrFail();
        $this->assertSame($user->id, $picture->user_id);
        $this->assertSame(800, $picture->width);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $picture->file_path));
    }

    public function test_unverified_users_cannot_upload(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/store/picture', ['title' => 'x'])
            ->assertForbidden();
    }

    public function test_upload_validates_the_file(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/store/picture', [
                'title' => 'Doc',
                'price' => 0,
                'category_id' => 123,
                'file' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price', 'category_id', 'file']);
    }

    public function test_the_owner_can_edit_a_picture(): void
    {
        $picture = Picture::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($picture->user, 'sanctum')
            ->putJson("/api/pictures/{$picture->id}", ['title' => 'New title', 'price' => 30, 'category_id' => $category->id])
            ->assertOk();

        $this->assertDatabaseHas('pictures', ['id' => $picture->id, 'title' => 'New title', 'price' => 30, 'category_id' => $category->id]);
    }

    public function test_other_users_cannot_edit_or_delete_a_picture(): void
    {
        $picture = Picture::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($stranger, 'sanctum')
            ->putJson("/api/pictures/{$picture->id}", ['title' => 'Hacked', 'price' => 1, 'category_id' => $picture->category_id])
            ->assertForbidden();
        $this->actingAs($stranger, 'sanctum')->deleteJson("/api/pictures/{$picture->id}")->assertForbidden();

        $this->assertDatabaseHas('pictures', ['id' => $picture->id, 'deleted_at' => null]);
    }

    public function test_deleting_an_unsold_picture_removes_it_for_good(): void
    {
        Storage::fake('public');
        $picture = Picture::factory()->create();

        $this->actingAs($picture->user, 'sanctum')->deleteJson("/api/pictures/{$picture->id}")->assertOk();

        $this->assertDatabaseMissing('pictures', ['id' => $picture->id]);
    }

    public function test_deleting_a_sold_picture_keeps_it_for_buyers(): void
    {
        $picture = Picture::factory()->create();
        $order = Order::factory()->for($picture)->create();

        $this->actingAs($picture->user, 'sanctum')->deleteJson("/api/pictures/{$picture->id}")->assertOk();

        $this->assertSoftDeleted('pictures', ['id' => $picture->id]);
        $this->getJson('/api/pictures')->assertJsonCount(0, 'data');
        $this->actingAs($order->user, 'sanctum')->getJson("/api/download/picture/{$picture->id}")->assertOk();
    }
}
