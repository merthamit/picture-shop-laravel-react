<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewController extends Controller
{

    public function index()
    {
        $reviews = Review::latest()->get();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggleReviewStatus(Review $review, $status)
    {
        $review->update([
            'approved' => $status,
        ]);

        redirect()->route('admin.reviews.index')->with([
            'success' => 'Review has been updated successfully.',
        ]);
    }

    public function destroy(Review $review)
    {
        $review->delete();

        redirect()->route('admin.reviews.index')->with([
            'success' => 'Review has been deleted successfully.',
        ]);
    }
}
