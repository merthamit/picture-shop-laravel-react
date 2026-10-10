<?php

namespace App\Services;

use App\Models\Picture;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores picture originals and builds the URLs used by the shop.
 *
 * With Cloudinary configured (CLOUDINARY_URL), originals are uploaded as
 * "authenticated" assets: they can never be fetched directly. The catalog
 * only sees signed, derived URLs (watermarked preview + small thumbnail)
 * served from Cloudinary's CDN, and buyers get a short-lived signed
 * download link to the original.
 *
 * Without Cloudinary (local dev / tests) files go to the public disk.
 */
class MediaService
{
    public const FOLDER = 'picture-shop/originals';

    // Repeated diagonally over the whole preview, like stock photo sites
    private const WATERMARK = 'l_text:Arial_56_bold:PICTURE%20SHOP%20%E2%80%A2%20PREVIEW,co_white,o_28,a_-30/fl_layer_apply,fl_tiled,g_center';
    private const PREVIEW = 'c_limit,w_1400,h_1400/q_auto,f_auto';
    private const THUMBNAIL = 'c_fill,g_auto,w_640,h_480/q_auto,f_auto';
    private const THUMB_MARK = 'l_text:Arial_26_bold:Picture%20Shop,co_white,o_60/fl_layer_apply,g_south_east,x_14,y_10';

    private ?Cloudinary $cloudinary = null;

    public function __construct()
    {
        $url = config('services.cloudinary.url');
        if ($url) {
            $this->cloudinary = new Cloudinary($url);
        }
    }

    public function usesCloudinary(): bool
    {
        return $this->cloudinary !== null;
    }

    /**
     * Store an uploaded file or a remote URL.
     *
     * @return array{disk: string, file_path: string, public_id: ?string, ext: string, width: ?int, height: ?int}
     */
    public function store(UploadedFile|string $source, ?string $publicId = null): array
    {
        if ($this->usesCloudinary()) {
            $result = $this->uploadApi()->upload(
                $source instanceof UploadedFile ? $source->getRealPath() : $source,
                array_filter([
                    'folder' => $publicId ? null : self::FOLDER,
                    'public_id' => $publicId ? self::FOLDER . '/' . $publicId : null,
                    'type' => 'authenticated',
                    'resource_type' => 'image',
                    'overwrite' => false,
                    'unique_filename' => true,
                ], fn ($value) => $value !== null)
            );

            return [
                'disk' => 'cloudinary',
                'public_id' => $result['public_id'],
                'file_path' => $result['public_id'],
                'ext' => strtolower($result['format'] ?? 'jpg'),
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
            ];
        }

        if (! $source instanceof UploadedFile) {
            throw new \RuntimeException('Remote sources require Cloudinary (set CLOUDINARY_URL).');
        }

        $fileName = time() . '_picture_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $source->getClientOriginalName());
        $source->storeAs('user/images', $fileName, 'public');
        [$width, $height] = @getimagesize($source->getRealPath()) ?: [null, null];

        return [
            'disk' => 'local',
            'public_id' => null,
            'file_path' => 'storage/user/images/' . $fileName,
            'ext' => strtolower($source->getClientOriginalExtension()),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function previewUrl(Picture $picture): string
    {
        return $picture->disk === 'cloudinary'
            ? $this->signed($picture->public_id, self::PREVIEW . '/' . self::WATERMARK)
            : $this->localUrl($picture);
    }

    public function thumbnailUrl(Picture $picture): string
    {
        return $picture->disk === 'cloudinary'
            ? $this->signed($picture->public_id, self::THUMBNAIL . '/' . self::THUMB_MARK)
            : $this->localUrl($picture);
    }

    /**
     * Short-lived link to the full resolution original (buyers / owner only).
     */
    public function downloadUrl(Picture $picture, int $ttlSeconds = 300): string
    {
        if ($picture->disk === 'cloudinary') {
            return $this->uploadApi()->privateDownloadUrl($picture->public_id, $picture->ext, [
                'type' => 'authenticated',
                'attachment' => true,
                'expires_at' => time() + $ttlSeconds,
            ]);
        }

        return $this->localUrl($picture);
    }

    public function delete(Picture $picture): void
    {
        if ($picture->disk === 'cloudinary' && $picture->public_id) {
            $this->uploadApi()->destroy($picture->public_id, ['type' => 'authenticated', 'invalidate' => true]);

            return;
        }

        Storage::disk('public')->delete(str_replace('storage/', '', $picture->file_path));
    }

    private function signed(string $publicId, string $transformation): string
    {
        return (string) $this->cloudinary->image($publicId)
            ->deliveryType('authenticated')
            ->addTransformation($transformation)
            ->signUrl(true)
            ->secure(true)
            ->toUrl();
    }

    private function localUrl(Picture $picture): string
    {
        $cdn = config('services.cdn.url');

        return $cdn
            ? rtrim($cdn, '/') . '/' . ltrim($picture->file_path, '/')
            : asset($picture->file_path);
    }

    private function uploadApi(): UploadApi
    {
        return $this->cloudinary->uploadApi();
    }
}
