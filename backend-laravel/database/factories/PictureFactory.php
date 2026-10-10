<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Picture>
 */
class PictureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucwords(fake()->words(3, true)),
            'price' => fake()->numberBetween(5, 60),
            'status' => 1,
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'file_path' => 'storage/user/images/' . fake()->uuid() . '.jpg',
            'disk' => 'local',
            'ext' => 'jpg',
            'width' => 1920,
            'height' => 1280,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['status' => 0]);
    }
}
