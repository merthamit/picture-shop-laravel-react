<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Picture;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_live_pictures_with_pagination(): void
    {
        Picture::factory()->count(14)->create();
        Picture::factory()->hidden()->create();
        Picture::factory()->create()->delete(); // removed by seller

        $this->getJson('/api/pictures?per_page=12')
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('meta.total', 14)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonStructure([
                'data' => [['id', 'title', 'price', 'image_path', 'thumbnail_path', 'category', 'user', 'rating']],
                'price_range' => ['min', 'max'],
            ]);
    }

    public function test_it_filters_by_categories_price_format_and_search(): void
    {
        [$nature, $city, $food] = Category::factory()->count(3)->create();
        $match = Picture::factory()->for($nature)->create(['title' => 'Golden Forest', 'price' => 20, 'ext' => 'jpg']);
        Picture::factory()->for($city)->create(['title' => 'Golden Bridge', 'price' => 20, 'ext' => 'jpg']);
        Picture::factory()->for($nature)->create(['title' => 'Golden Lake', 'price' => 90, 'ext' => 'jpg']);
        Picture::factory()->for($nature)->create(['title' => 'Golden Field', 'price' => 20, 'ext' => 'png']);
        $other = Picture::factory()->for($food)->create(['title' => 'Golden Honey', 'price' => 15, 'ext' => 'JPG']);

        $response = $this->getJson('/api/pictures?' . http_build_query([
            'q' => 'golden',
            'categories' => "{$nature->id},{$food->id}",
            'min_price' => 10,
            'max_price' => 30,
            'ext' => 'jpg',
        ]));

        $response->assertOk();
        $this->assertEqualsCanonicalizing([$match->id, $other->id], collect($response->json('data'))->pluck('id')->all());
    }

    public function test_it_sorts_by_price_popularity_and_rating(): void
    {
        $cheap = Picture::factory()->create(['price' => 5]);
        $expensive = Picture::factory()->create(['price' => 50]);
        $popular = Picture::factory()->create(['price' => 20]);
        Order::factory()->count(3)->for($popular)->create();
        Review::create(['user_id' => $cheap->user_id, 'picture_id' => $cheap->id, 'rating' => 5, 'comment' => 'Great', 'approved' => 1]);
        Review::create(['user_id' => $expensive->user_id, 'picture_id' => $expensive->id, 'rating' => 2, 'comment' => 'Meh', 'approved' => 1]);

        $ids = fn ($sort) => collect($this->getJson("/api/pictures?sort={$sort}")->json('data'))->pluck('id')->all();

        $this->assertSame([$expensive->id, $popular->id, $cheap->id], $ids('price_desc'));
        $this->assertSame($popular->id, $ids('popular')[0]);
        $this->assertSame([$cheap->id, $expensive->id], array_slice($ids('rating'), 0, 2));
    }

    public function test_it_rejects_an_unknown_sort(): void
    {
        $this->getJson('/api/pictures?sort=hack')->assertUnprocessable();
    }

    public function test_categories_include_live_picture_counts(): void
    {
        $nature = Category::factory()->create(['name' => 'Nature']);
        Category::factory()->create(['name' => 'Empty']);
        Picture::factory()->count(2)->for($nature)->create();
        Picture::factory()->hidden()->for($nature)->create();

        $this->getJson('/api/categories/1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Nature')
            ->assertJsonPath('data.0.pictures_count', 2);
    }

    public function test_hidden_pictures_are_not_found_for_visitors(): void
    {
        $picture = Picture::factory()->hidden()->create();

        $this->getJson("/api/pictures/{$picture->id}")->assertNotFound();
        $this->actingAs($picture->user, 'sanctum')->getJson("/api/pictures/{$picture->id}")->assertOk();
    }
}
