# Picture Shop: Stock Photo Marketplace

**Laravel 11 · React 18 · MySQL · Redis · RabbitMQ · Cloudinary CDN · Docker · Stripe**

Picture Shop is a stock-photo marketplace. Creators upload and sell photos. Buyers browse watermarked previews, pay with Stripe and download the full-resolution original. An admin panel moderates the content.

The site is available in **English and Turkish**.

![Tests](../../actions/workflows/tests.yml/badge.svg)

## Features

**Storefront (React)**
- Catalog search, multi-category, format and price filters, six sort orders, and server-side pagination. Filters are stored in the URL so you can share a filtered view.
- Language switcher (🇬🇧 / 🇹🇷). It translates the UI and the API messages. The language is saved on the account, so e-mails arrive in it too.
- Wishlist (❤️) and a favorites page.
- Picture detail page with a tiled watermark preview, resolution and sales info, reviews and related pictures.
- Cart stored in Redis for logged-in users. A guest cart is merged into it on login.
- Stripe checkout.
- Profile page with stats, purchases (with order status) and the user's own pictures, which can be edited or deleted.
- Accounts: register, e-mail verification, forgot/reset password. Selling and buying require a verified e-mail.

**Admin panel (Blade)**: dashboard with revenue chart, best sellers and recent orders. Orders, pictures, categories, review moderation and users, all with search, filters and pagination.

## Architecture

```
                 ┌──────────────┐            ┌────────────────────────────┐
  Browser ─────▶ │ frontend     │            │ Cloudinary (storage + CDN) │
                 │ React (Vite) │◀── images ─│ originals: private         │
                 └──────┬───────┘            │ previews: signed + watermark│
                        │ REST /api          └─────────────▲──────────────┘
                        ▼                                  │ upload / signed URLs
                 ┌──────────────────────────────────────┐  │
                 │ web (nginx) ─▶ app (Laravel PHP-FPM) │──┘
                 └──────┬───────────┬──────────┬────────┘
                        ▼           ▼          ▼ publish
                     MySQL       Redis      RabbitMQ ──▶ queue-worker ──▶ mailpit (SMTP)
                              cache/cart   orders, notifications
```

| Concern | Implementation |
|---|---|
| REST API | Laravel 11 + Sanctum tokens, Form Requests, API Resources (`backend-laravel/routes/api.php`) |
| Media and CDN | `app/Services/MediaService.php` uploads originals to Cloudinary as `authenticated` assets, which cannot be fetched directly. The shop only uses **signed** derived URLs: a 1400px preview with a tiled watermark and a 640×480 thumbnail. Buyers get a **5-minute signed download link** to the original. Without `CLOUDINARY_URL` the app falls back to local disk behind an nginx CDN container. |
| Caching | Redis caches categories (with picture counts), formats and the price range (`app/Services/CatalogCache.php`). Model events invalidate it. |
| Cart sessions | Redis hash `cart:{userId}` with a sliding TTL (`app/Services/CartService.php`) |
| Async work | An order is written as `pending` and `ProcessOrder` is published to the RabbitMQ `orders` queue. The worker completes the order and queues e-mails (order receipt, "your picture sold", verification, password reset) on the `notifications` queue. |
| i18n | API: `SetLocale` middleware + `lang/{en,tr}`. UI: `src/i18n` with `useT()`. A test checks that both languages have the same keys. |
| Security | Ownership checks for edit, delete and download. Reviews and uploads always use the authenticated user. Prices come from the database, never from the client. Admin routes sit behind the `admin` middleware. Sensitive endpoints are rate-limited. Pictures that were already sold are soft-deleted so buyers keep their downloads. |

## Run with Docker

```bash
cp .env.example .env          # add your Cloudinary URL and Stripe test keys
docker compose up -d --build
docker compose exec app php artisan db:seed --class=DemoSeeder   # optional demo data
```

The demo seeder creates 30 users, about 157 real photos (Unsplash license, via picsum.photos) uploaded to Cloudinary, and orders, reviews and favorites.

| Service | URL |
|---|---|
| Shop (React) | http://localhost:5173 (`buyer@demo.test` / `password`, `seller@demo.test` / `password`) |
| Admin panel | http://localhost:8000 (`admin@laravel.com` / `123456`) |
| RabbitMQ management | http://localhost:15672 (`picture_shop` / `secret`) |
| Mailpit (sent e-mails: verification, reset, receipts) | http://localhost:8025 |
| MySQL | `localhost:3307` (`picture_shop` / `secret`) |

### Environment (`.env` next to `docker-compose.yml`)

| Variable | Purpose |
|---|---|
| `CLOUDINARY_URL` | `cloudinary://<api_key>:<api_secret>@<cloud_name>` from the Cloudinary console |
| `STRIPE_SECRET` | Stripe **test** secret key (`sk_test_...`) |
| `VITE_STRIPE_PUBLISHABLE_KEY` | Stripe test publishable key (`pk_test_...`) |

For a test payment, use card `4242 4242 4242 4242` with any future date and any CVC.

## Tests

```bash
# Backend: 40 PHPUnit tests (in-memory SQLite; Redis for the cart tests)
docker compose run --rm --no-deps -e RUN_MIGRATIONS=false app php artisan test

# Frontend: Vitest + Testing Library
cd frontend-react && npm test
```

The tests cover catalog filters and sorting, upload, edit and delete permissions, signed downloads, favorites, the order → queue → notification flow, e-mail verification and password reset, translations, the Redis cart, review spoofing and admin access. GitHub Actions runs both suites on every push.

## Project structure

```
backend-laravel/    Laravel API, admin panel, queue jobs, notifications, tests
frontend-react/     React storefront (Redux Toolkit, React Router, Stripe Elements)
docker/cdn/         nginx edge cache used when Cloudinary is not configured
docker-compose.yml  app, queue-worker, web, cdn, frontend, mysql, redis, rabbitmq, mailpit
```
