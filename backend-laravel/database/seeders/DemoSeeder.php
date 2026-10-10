<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\Picture;
use App\Models\Review;
use App\Models\User;
use App\Services\CatalogCache;
use App\Services\MediaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Demo marketplace: users, real photos (Unsplash-licensed, via picsum.photos)
 * uploaded to Cloudinary, plus orders, reviews and favorites.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Cloudinary fetches each photo itself from the remote URL, and stable
 * public IDs make re-running the seeder skip files that already exist.
 */
class DemoSeeder extends Seeder
{
    private const COMMENTS = [
        ['Absolutely stunning, perfect for my blog header.', 5],
        ['Beautiful colors and great resolution.', 5],
        ['Exactly what I needed for a client presentation.', 5],
        ['Harika bir kare, sunumumda kullandım.', 5],
        ['Renkleri çok canlı, kesinlikle tavsiye ederim.', 5],
        ['Very good quality, quick download.', 4],
        ['Nice shot, works well as a wallpaper.', 4],
        ['Güzel bir fotoğraf, fiyatına göre çok iyi.', 4],
        ['Good, but a bit darker than I expected.', 3],
        ['Fena değil ama biraz daha keskin olabilirdi.', 3],
    ];

    public function run(MediaService $media): void
    {
        if (!$media->usesCloudinary()) {
            $this->command->error('CLOUDINARY_URL is not set: the demo photos are uploaded to Cloudinary.');
            return;
        }

        $this->call(CategorySeeder::class);
        $categories = Category::pluck('id', 'name');

        // --- Users ----------------------------------------------------------
        $buyer = User::updateOrCreate(['email' => 'buyer@demo.test'], [
            'name' => 'Deniz Yılmaz', 'password' => Hash::make('password'), 'locale' => 'tr',
        ]);
        $seller = User::updateOrCreate(['email' => 'seller@demo.test'], [
            'name' => 'Alex Morgan', 'password' => Hash::make('password'), 'locale' => 'en',
        ]);
        $buyer->markEmailAsVerified();
        $seller->markEmailAsVerified();

        $others = User::factory(28)->create()->each(function (User $user, $index) {
            $user->update(['locale' => $index % 3 === 0 ? 'tr' : 'en']);
        });
        $users = $others->concat([$buyer, $seller]);
        $creators = $others->take(12)->push($seller);

        // --- Pictures (uploaded to Cloudinary) ------------------------------
        $items = json_decode(file_get_contents(database_path('seeders/data/demo_pictures.json')), true);
        $bar = $this->command->getOutput()->createProgressBar(count($items));
        $this->command->info('Uploading ' . count($items) . ' photos to Cloudinary...');
        $bar->start();

        $pictures = collect();
        foreach ($items as $item) {
            $stored = $media->store(
                "https://picsum.photos/id/{$item['picsum_id']}/1920/1280",
                'picsum-' . $item['picsum_id']
            );

            $picture = Picture::create([
                'title' => $item['title'],
                'price' => $this->price(),
                'user_id' => $creators->random()->id,
                'category_id' => $categories[$item['category']],
                ...$stored,
                'ext' => 'jpg',
            ]);
            $picture->forceFill(['created_at' => Carbon::now()->subDays(rand(0, 60))->subMinutes(rand(0, 1440))])->saveQuietly();
            $pictures->push($picture);
            $bar->advance();
        }
        $bar->finish();
        $this->command->newLine();

        // --- Orders, reviews, favorites --------------------------------------
        foreach ($users as $user) {
            $candidates = $pictures->where('user_id', '!=', $user->id);
            $bought = $candidates->random(min(rand(1, 7), $candidates->count()));

            foreach ($bought as $picture) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'picture_id' => $picture->id,
                    'total' => $picture->price,
                    'status' => Order::STATUS_COMPLETED,
                ]);
                $order->forceFill(['created_at' => Carbon::now()->subDays(rand(0, 13))->subMinutes(rand(0, 1440))])->saveQuietly();

                if (rand(1, 100) <= 55) {
                    [$comment, $rating] = self::COMMENTS[array_rand(self::COMMENTS)];
                    Review::create([
                        'user_id' => $user->id,
                        'picture_id' => $picture->id,
                        'comment' => $comment,
                        'rating' => $rating,
                        'approved' => rand(1, 100) <= 85,
                    ]);
                }
            }

            foreach ($candidates->random(min(rand(0, 6), $candidates->count())) as $picture) {
                Favorite::firstOrCreate(['user_id' => $user->id, 'picture_id' => $picture->id]);
            }
        }

        CatalogCache::flush();

        $this->command->info(sprintf(
            'Demo ready: %d users, %d pictures, %d orders, %d reviews.',
            User::count(), Picture::count(), Order::count(), Review::count()
        ));
        $this->command->info('Log in as buyer@demo.test / password or seller@demo.test / password');
    }

    private function price(): int
    {
        return [5, 8, 9, 10, 12, 14, 15, 18, 19, 20, 24, 25, 29, 30, 35, 39, 45, 49, 55, 65][array_rand(range(0, 19))];
    }
}
