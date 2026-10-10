<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImageRequest;
use App\Http\Requests\UpdatePictureRequest;
use App\Http\Resources\PictureCardResource;
use App\Http\Resources\PictureResource;
use App\Http\Resources\UserResource;
use App\Models\Picture;
use App\Services\CatalogCache;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PictureController extends Controller
{
    public const SORTS = ['newest', 'oldest', 'price_asc', 'price_desc', 'popular', 'rating'];

    public function __construct(private MediaService $media)
    {
    }

    /**
     * Paginated catalog listing with search, filters and sorting, e.g.
     * /pictures?q=sun&categories=1,3&ext=png,jpg&min_price=5&max_price=50&sort=popular&page=2
     */
    public function index(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'categories' => 'nullable|string',
            'ext' => 'nullable|string',
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0',
            'sort' => 'nullable|in:' . implode(',', self::SORTS),
            'per_page' => 'nullable|integer|min:1|max:48',
            'ids' => 'nullable|string',
        ]);

        $approved = fn ($query) => $query->where('approved', 1);
        $csv = fn ($value) => array_values(array_filter(array_map('trim', explode(',', (string) $value)), 'strlen'));

        $query = Picture::query()
            ->where('status', 1)
            ->with(['user:id,name', 'category:id,name'])
            ->withCount(['orders', 'reviews' => $approved])
            ->withAvg(['reviews as rating' => $approved], 'rating')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->when($request->filled('categories'), fn ($q) => $q->whereIn('category_id', $csv($request->categories)))
            ->when($request->filled('ext'), fn ($q) => $q->whereIn(DB::raw('LOWER(ext)'), array_map('strtolower', $csv($request->ext))))
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', (int) $request->min_price))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', (int) $request->max_price))
            ->when($request->filled('ids'), fn ($q) => $q->whereIn('id', $csv($request->ids)));

        match ($request->input('sort', 'newest')) {
            'oldest' => $query->oldest()->orderBy('id'),
            'price_asc' => $query->orderBy('price')->latest(),
            'price_desc' => $query->orderByDesc('price')->latest(),
            'popular' => $query->orderByDesc('orders_count')->latest(),
            'rating' => $query->orderByDesc('rating')->orderByDesc('reviews_count')->latest(),
            default => $query->latest()->orderByDesc('id'),
        };

        $pictures = $query->paginate($request->integer('per_page', 12))->withQueryString();

        return PictureCardResource::collection($pictures)->additional([
            'price_range' => CatalogCache::priceRange(),
        ]);
    }

    // Fetch pictures by Id
    public function fetchById(Request $request, Picture $picture)
    {
        // Hidden pictures stay visible to their owner only
        if (!$picture->status && !$picture->isOwnedBy($request->user('sanctum'))) {
            abort(404);
        }
        return PictureResource::make($picture);
    }

    public function fetchExtentions()
    {
        return response()->json(CatalogCache::extensions());
    }

    public function uploadFile(StoreImageRequest $request)
    {
        $user = $request->user();
        $media = $this->media->store($request->file('file'));

        Picture::create([
            'title' => $request->title,
            'price' => $request->price,
            'user_id' => $user->id,
            'category_id' => $request->category_id,
            ...$media,
        ]);

        return response([
            'message' => __('messages.picture_saved'),
            'user' => UserResource::make($user),
        ]);
    }

    public function update(UpdatePictureRequest $request, Picture $picture)
    {
        $picture->update($request->only('title', 'price', 'category_id'));

        return response()->json([
            'message' => __('messages.picture_updated'),
            'user' => UserResource::make($request->user()),
        ]);
    }

    /**
     * Pictures nobody bought are removed for good (including the file);
     * sold ones are only taken off the shop so buyers keep their downloads.
     */
    public function destroy(Request $request, Picture $picture)
    {
        abort_unless($picture->isOwnedBy($request->user()), 403);

        if ($picture->orders()->exists()) {
            $picture->delete();
        } else {
            $this->media->delete($picture);
            $picture->forceDelete();
        }

        return response()->json([
            'message' => __('messages.picture_deleted'),
            'user' => UserResource::make($request->user()),
        ]);
    }

    /**
     * Returns a short-lived signed link to the original file, only for
     * buyers and the owner.
     */
    public function downloadPicture(Request $request, Picture $picture)
    {
        $user = $request->user();
        abort_unless($picture->isOwnedBy($user) || $picture->isPurchasedBy($user), 403, __('messages.download_forbidden'));

        return response()->json([
            'url' => $this->media->downloadUrl($picture),
            'filename' => $picture->title . '.' . $picture->ext,
        ]);
    }
}
