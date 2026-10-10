<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'locale' => $this->locale,
            'email_verified' => $this->hasVerifiedEmail(),
            'image_path' => $this->image_path,
            'favorite_ids' => $this->favorites()->pluck('picture_id'),
            'orders' => OrderResource::collection($this->orders()->with('picture')->latest()->get()),
            'pictures' => $this->pictures()
                ->with('category:id,name')
                ->withCount('orders')
                ->latest()
                ->get()
                ->map(fn ($picture) => [
                    'id' => $picture->id,
                    'title' => $picture->title,
                    'price' => $picture->price,
                    'status' => $picture->status,
                    'category_id' => $picture->category_id,
                    'category' => $picture->category?->only(['id', 'name']),
                    'thumbnail_path' => $picture->thumbnail_path,
                    'sales_count' => $picture->orders_count,
                ]),
        ];
    }
}
