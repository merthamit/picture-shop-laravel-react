<?php

namespace App\Models;

use App\Services\CatalogCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    protected static function booted(): void
    {
        static::saved(fn () => CatalogCache::flush());
        static::deleted(fn () => CatalogCache::flush());
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    // Display name in the current language (names are stored in English)
    public function getTranslatedNameAttribute(): string
    {
        $key = "categories.{$this->name}";

        return Lang::has($key) ? __($key) : $this->name;
    }
}
