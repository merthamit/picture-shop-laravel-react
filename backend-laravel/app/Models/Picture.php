<?php

namespace App\Models;

use App\Services\CatalogCache;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Picture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'user_id', 'price', 'category_id', 'status',
        'file_path', 'disk', 'public_id', 'ext', 'width', 'height',
    ];
    protected $appends = ['image_path', 'thumbnail_path'];
    protected $hidden = ['file_path', 'public_id', 'disk'];

    protected static function booted(): void
    {
        static::saved(fn () => CatalogCache::flush());
        static::deleted(fn () => CatalogCache::flush());
        static::restored(fn () => CatalogCache::flush());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Watermarked, size-limited preview (never the original).
     * On Cloudinary this is a signed CDN URL.
     */
    public function getImagePathAttribute()
    {
        return app(MediaService::class)->previewUrl($this);
    }

    public function getThumbnailPathAttribute()
    {
        return app(MediaService::class)->thumbnailUrl($this);
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user && $this->user_id === $user->id;
    }

    public function isPurchasedBy(?User $user): bool
    {
        return $user && $this->orders()->where('user_id', $user->id)->exists();
    }
}
