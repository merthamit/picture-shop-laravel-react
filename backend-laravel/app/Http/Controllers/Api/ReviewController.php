<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $review = Review::where([
            'picture_id' => $request->picture_id,
            'user_id' => $request->user_id,
        ])->first();

        $order = Order::where([
            'picture_id' => $request->picture_id,
            'user_id' => $request->user_id,
        ])->first();

        if ($review) {
            return response()->json([
                'error' => 'You have already review this picture.',
            ]);
        }

        if (!$order) {
            return response()->json([
                'error' => 'You can review only the pictures that you bought.',
            ]);
        }

        $data = $request->all();
        Review::create($data);
        return response()->json([
            'message' => 'Your review has been added.',
        ]);
    }

    public function updateReview(Request $request)
    {
        $review = Review::where([
            'picture_id' => $request->picture_id,
            'user_id' => $request->user_id,
        ])->first();

        if ($review) {
            $review->update([
                'comment' => $request->comment,
                'rating' => $request->rating,
                'approved' => 0,
            ]);

            return response()->json([
                'message' => 'Your review has been updated.',
            ]);
        }
        return response()->json([
            'error' => 'Something went wrong.',
        ]);
    }

    public function deleteReview(Request $request)
    {
        $review = Review::where([
            'picture_id' => $request->picture_id,
            'user_id' => $request->user_id,
        ])->first();

        if ($review) {
            $review->delete();
            return response()->json([
                'message' => 'Your review has been deleted.',
            ]);
        }
        return response()->json([
            'error' => 'Something went wrong.',
        ]);
    }
}
