<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lightweight picture representation for listings (grid cards).
 * Uses eager-loaded counts/averages instead of loading every relation.
 */
class PictureCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'ext' => strtolower($this->ext),
            'image_path' => $this->image_path,
            'thumbnail_path' => $this->thumbnail_path,
            'category' => $this->category?->only(['id', 'name']),
            'user' => $this->user?->only(['id', 'name']),
            'sales_count' => $this->orders_count ?? 0,
            'reviews_count' => $this->reviews_count ?? 0,
            'rating' => $this->rating !== null ? round((float) $this->rating, 1) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
