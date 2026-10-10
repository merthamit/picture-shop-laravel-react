<?php

namespace App\Services;

use App\Models\Picture;
use Illuminate\Support\Facades\Redis;

/**
 * Shopping cart sessions stored in Redis as a hash per user:
 *   cart:{userId} => { pictureId => json(item) }
 * The key expires after CART_TTL seconds of inactivity.
 */
class CartService
{
    public function __construct(private int $ttl = 0)
    {
        $this->ttl = $ttl ?: (int) config('services.cart.ttl', 60 * 60 * 24 * 7);
    }

    private function key(int $userId): string
    {
        return "cart:{$userId}";
    }

    public function items(int $userId): array
    {
        $items = Redis::hgetall($this->key($userId)) ?: [];

        return array_values(array_map(fn ($item) => json_decode($item, true), $items));
    }

    public function add(int $userId, Picture $picture): array
    {
        $key = $this->key($userId);

        Redis::hset($key, $picture->id, json_encode([
            'id' => $picture->id,
            'title' => $picture->title,
            'price' => $picture->price,
            'image' => $picture->thumbnail_path,
        ]));
        Redis::expire($key, $this->ttl);

        return $this->items($userId);
    }

    public function remove(int $userId, int $pictureId): array
    {
        Redis::hdel($this->key($userId), $pictureId);

        return $this->items($userId);
    }

    public function clear(int $userId): void
    {
        Redis::del($this->key($userId));
    }
}
