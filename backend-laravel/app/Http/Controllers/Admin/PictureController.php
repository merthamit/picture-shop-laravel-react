<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Picture;
use Illuminate\Http\Request;

class PictureController extends Controller
{
    /**
     * Display all the pictures
     */
    public function index(Request $request)
    {
        $pictures = Picture::withTrashed()
            ->with(['user:id,name', 'category:id,name'])
            ->withCount('orders')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->when($request->status === 'live', fn ($q) => $q->where('status', 1)->whereNull('deleted_at'))
            ->when($request->status === 'hidden', fn ($q) => $q->where('status', 0))
            ->when($request->status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.pictures.index', [
            'pictures' => $pictures,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Change the status of the picture
     */
    public function togglePictureStatus(Picture $picture, $status)
    {
        $picture->update([
            'status' => (int) (bool) $status,
        ]);

        return back()->with([
            'success' => 'Picture has been updated successfully',
        ]);
    }
}
