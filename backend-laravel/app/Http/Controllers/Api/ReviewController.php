<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

/**
 * Reviews always belong to the authenticated user; the user_id sent by
 * the client is ignored so nobody can write or delete in someone's name.
 */
class ReviewController extends Controller
{
    private function rules(): array
    {
        return [
            'picture_id' => 'required|integer|exists:pictures,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        $userId = $request->user()->id;

        $review = Review::where([
            'picture_id' => $request->picture_id,
            'user_id' => $userId,
        ])->first();

        $order = Order::where([
            'picture_id' => $request->picture_id,
            'user_id' => $userId,
        ])->first();

        if ($review) {
            return response()->json([
                'error' => __('messages.review_exists'),
            ]);
        }

        if (!$order) {
            return response()->json([
                'error' => __('messages.review_buyers_only'),
            ]);
        }

        Review::create([
            'picture_id' => $request->picture_id,
            'user_id' => $userId,
            'rating' => $request->rating,
            'comment' => $request->comment,
            // Published right away; admins can still hide it from the panel
            'approved' => 1,
        ]);
        return response()->json([
            'message' => __('messages.review_added'),
        ]);
    }

    public function updateReview(Request $request)
    {
        $request->validate($this->rules());

        $review = Review::where([
            'picture_id' => $request->picture_id,
            'user_id' => $request->user()->id,
        ])->first();

        if ($review) {
            $review->update([
                'comment' => $request->comment,
                'rating' => $request->rating,
            ]);

            return response()->json([
                'message' => __('messages.review_updated'),
            ]);
        }
        return response()->json([
            'error' => __('messages.something_wrong'),
        ]);
    }

    public function deleteReview(Request $request)
    {
        $review = Review::where([
            'picture_id' => $request->picture_id,
            'user_id' => $request->user()->id,
        ])->first();

        if ($review) {
            $review->delete();
            return response()->json([
                'message' => __('messages.review_deleted'),
            ]);
        }
        return response()->json([
            'error' => __('messages.something_wrong'),
        ]);
    }
}
