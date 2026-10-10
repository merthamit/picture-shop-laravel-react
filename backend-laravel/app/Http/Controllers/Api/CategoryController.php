<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CatalogCache;

class CategoryController extends Controller
{
    public function index($hasPictures)
    {
        // Served from the Redis cache; only categories that have pictures when $hasPictures is truthy
        return CategoryResource::collection(CatalogCache::categories((bool) $hasPictures));
    }
}
