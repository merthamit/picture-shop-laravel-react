<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PictureCardResource;
use App\Models\Favorite;
use App\Models\Picture;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $approved = fn ($query) => $query->where('approved', 1);

        $pictures = Picture::query()
            ->where('status', 1)
            ->whereHas('favorites', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['user:id,name', 'category:id,name'])
            ->withCount(['orders', 'reviews' => $approved])
            ->withAvg(['reviews as rating' => $approved], 'rating')
            ->latest()
            ->get();

        return PictureCardResource::collection($pictures);
    }

    public function toggle(Request $request, Picture $picture)
    {
        $user = $request->user();
        $favorite = Favorite::where(['user_id' => $user->id, 'picture_id' => $picture->id])->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            Favorite::create(['user_id' => $user->id, 'picture_id' => $picture->id]);
        }

        return response()->json([
            'favorited' => !$favorite,
            'favorite_ids' => $user->favorites()->pluck('picture_id'),
            'message' => __($favorite ? 'messages.favorite_removed' : 'messages.favorite_added'),
        ]);
    }
}
