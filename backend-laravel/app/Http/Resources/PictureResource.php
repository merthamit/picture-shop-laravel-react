<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PictureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user('sanctum');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'pictures_count' => $this->user->pictures()->where('status', 1)->count(),
            ],
            'category' => $this->category?->only(['id', 'name']),
            'category_id' => $this->category_id,
            'image_path' => $this->image_path,
            'thumbnail_path' => $this->thumbnail_path,
            'ext' => strtolower($this->ext),
            'width' => $this->width,
            'height' => $this->height,
            'status' => $this->status,
            'is_deleted' => $this->trashed(),
            'sales_count' => $this->orders()->count(),
            'favorites_count' => $this->favorites()->count(),
            'is_owner' => $this->isOwnedBy($viewer),
            'is_purchased' => $this->isPurchasedBy($viewer),
            'reviews' => $this->reviews()->with('user:id,name')->where('approved', 1)->latest()->get(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
