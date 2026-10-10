<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display all the reviews (pending first)
     */
    public function index(Request $request)
    {
        $reviews = Review::with(['user:id,name', 'picture' => fn ($q) => $q->withTrashed()])
            ->when($request->status === 'pending', fn ($q) => $q->where('approved', 0))
            ->when($request->status === 'approved', fn ($q) => $q->where('approved', 1))
            ->orderBy('approved')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'pendingCount' => Review::where('approved', 0)->count(),
        ]);
    }

    public function toggleReviewStatus(Review $review, $status)
    {
        $review->update([
            'approved' => (int) (bool) $status,
        ]);

        return back()->with([
            'success' => 'Review has been updated successfully.',
        ]);
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with([
            'success' => 'Review has been deleted successfully.',
        ]);
    }
}
