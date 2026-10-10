<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'picture_id' => Picture::factory(),
            'total' => 10,
            'status' => Order::STATUS_COMPLETED,
        ];
    }
}
