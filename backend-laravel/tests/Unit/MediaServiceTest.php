<?php

namespace Tests\Unit;

use App\Models\Picture;
use App\Services\MediaService;
use Cloudinary\Cloudinary;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    private function cloudinaryPicture(): Picture
    {
        return new Picture([
            'disk' => 'cloudinary',
            'public_id' => 'picture-shop/originals/picsum-1',
            'ext' => 'jpg',
        ]);
    }

    private function cloudinaryMedia(): MediaService
    {
        config(['services.cloudinary.url' => 'cloudinary://123456:secret@demo-cloud']);

        return new MediaService;
    }

    public function test_local_pictures_are_served_from_the_cdn_url(): void
    {
        config(['services.cloudinary.url' => null, 'services.cdn.url' => 'https://cdn.test']);
        $picture = new Picture(['disk' => 'local', 'file_path' => 'storage/user/images/a.jpg']);

        $this->assertSame('https://cdn.test/storage/user/images/a.jpg', (new MediaService)->previewUrl($picture));
    }

    public function test_previews_are_signed_watermarked_and_never_the_original(): void
    {
        $url = $this->cloudinaryMedia()->previewUrl($this->cloudinaryPicture());

        $this->assertStringStartsWith('https://res.cloudinary.com/demo-cloud/image/authenticated/s--', $url);
        $this->assertStringContainsString('l_text:', $url);
        $this->assertStringContainsString('fl_tiled', $url);
        $this->assertStringContainsString('c_limit,w_1400', $url);
    }

    public function test_thumbnails_are_small_signed_crops(): void
    {
        $url = $this->cloudinaryMedia()->thumbnailUrl($this->cloudinaryPicture());

        $this->assertStringContainsString('/authenticated/s--', $url);
        $this->assertStringContainsString('c_fill,g_auto,w_640,h_480', $url);
    }

    public function test_download_links_expire(): void
    {
        $url = $this->cloudinaryMedia()->downloadUrl($this->cloudinaryPicture(), 120);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame('authenticated', $query['type']);
        $this->assertEqualsWithDelta(time() + 120, (int) $query['expires_at'], 5);
        $this->assertArrayHasKey('signature', $query);
    }
}
