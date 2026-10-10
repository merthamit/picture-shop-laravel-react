<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'picture' => [
                'id' => $this->picture->id,
                'title' => $this->picture->title,
                'price' => $this->picture->price,
                'thumbnail_path' => $this->picture->thumbnail_path,
                'is_deleted' => $this->picture->trashed(),
            ],
            'total' => $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
