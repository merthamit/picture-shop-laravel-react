<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::withCount('pictures')->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        if ($request->validated()) {
            Category::create([
                'name' => $request->name,
            ]);
        }
        return redirect()->route('admin.categories.index')->with(['success' => __('admin.flash.category_created')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        if ($request->validated()) {
            $category->update([
                'name' => $request->name,
            ]);
        }
        return redirect()->route('admin.categories.index')->with(['success' => __('admin.flash.category_updated')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Deleting would cascade to its pictures and to the buyers' orders
        if ($category->pictures()->withTrashed()->exists()) {
            return back()->with(['error' => __('admin.flash.category_has_pictures')]);
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with(['success' => __('admin.flash.category_deleted')]);
    }
}
