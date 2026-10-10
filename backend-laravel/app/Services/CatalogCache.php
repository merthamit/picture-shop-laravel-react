<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Picture;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Redis-backed cache for frequently queried catalog data.
 * Entries are invalidated by model events (see Category / Picture models).
 */
class CatalogCache
{
    public const TTL = 3600;

    public const CATEGORIES_ALL = 'catalog:categories:all';
    public const CATEGORIES_WITH_PICTURES = 'catalog:categories:with_pictures';
    public const EXTENSIONS = 'catalog:extensions';
    public const PRICE_RANGE = 'catalog:price_range';

    public static function categories(bool $onlyWithPictures)
    {
        if ($onlyWithPictures) {
            // Only categories that have live pictures, with how many they have
            return Cache::remember(self::CATEGORIES_WITH_PICTURES, self::TTL, function () {
                $live = fn ($query) => $query->where('status', 1);

                return Category::whereHas('pictures', $live)
                    ->withCount(['pictures' => $live])
                    ->orderBy('name')
                    ->get();
            });
        }

        return Cache::remember(self::CATEGORIES_ALL, self::TTL, function () {
            return Category::orderBy('name')->get();
        });
    }

    public static function extensions()
    {
        return Cache::remember(self::EXTENSIONS, self::TTL, function () {
            return DB::table('pictures')
                ->where('status', 1)
                ->selectRaw('LOWER(ext) as ext, COUNT(*) as pictures_count')
                ->groupByRaw('LOWER(ext)')
                ->orderBy('ext')
                ->get();
        });
    }

    public static function priceRange(): array
    {
        return Cache::remember(self::PRICE_RANGE, self::TTL, function () {
            $range = Picture::where('status', 1)
                ->selectRaw('MIN(price) as min, MAX(price) as max')
                ->toBase()
                ->first();

            return [
                'min' => (int) ($range->min ?? 0),
                'max' => (int) ($range->max ?? 0),
            ];
        });
    }

    public static function flush(): void
    {
        Cache::deleteMultiple([
            self::CATEGORIES_ALL,
            self::CATEGORIES_WITH_PICTURES,
            self::EXTENSIONS,
            self::PRICE_RANGE,
        ]);
    }
}
