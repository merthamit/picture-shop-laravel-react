<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index($hasPictures)
    {
        if ($hasPictures) {
            // Only categories that have picture

            return CategoryResource::collection(Category::has('pictures')->get());
        }
        return CategoryResource::collection(Category::all());
    }
}
