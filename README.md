<div align="center">

# 📸 Picture Shop

**Stock Photo Marketplace · Stok Fotoğraf Pazaryeri**

Laravel 11 · React 18 · MySQL · Redis · RabbitMQ · Cloudinary CDN · Stripe · Docker

[![Tests](https://github.com/merthamit/picture-shop-laravel-react/actions/workflows/tests.yml/badge.svg)](https://github.com/merthamit/picture-shop-laravel-react/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![React](https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black)
![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis&logoColor=white)
![RabbitMQ](https://img.shields.io/badge/RabbitMQ-3.13-FF6600?logo=rabbitmq&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)

**[🇹🇷 Türkçe](#-türkçe)** · **[🇬🇧 English](#-english)**

</div>

---

## 🇹🇷 Türkçe

### Uygulama ne işe yarıyor?

**Picture Shop**, fotoğrafçıların çektikleri fotoğrafları satabildiği, tasarımcıların, blog yazarlarının ve şirketlerin de projeleri için telifsiz görsel satın alabildiği bir **stok fotoğraf pazaryeridir**. Shutterstock ve Adobe Stock gibi platformların küçük ölçekli bir benzeridir.

| Kim? | Ne yapar? |
|---|---|
| 🧑‍🎨 **Fotoğrafçı (satıcı)** | Fotoğraflarını yükler, fiyat ve kategori belirler, satışlarını ve kazancını takip eder, fotoğraflarını düzenler veya siler. Her satışta e-posta bildirimi alır. |
| 🛒 **Alıcı** | Kataloğu arar ve filtreler, beğendiklerini favorilere ekler, sepete atar, Stripe ile öder ve fotoğrafın **filigransız tam çözünürlüklü orijinalini** indirir. Satın aldığı fotoğrafa yorum ve puan verebilir. |
| 🛡️ **Yönetici (admin)** | Satış grafiklerini izler. Siparişleri, fotoğrafları, kategorileri ve kullanıcıları yönetir, yorumları onaylar. |

Satın alınmamış fotoğraflar sitede **yalnızca filigranlı önizleme** olarak görünür. Orijinal dosyaya sadece satın alan kişi kısa süreli, imzalı bir bağlantıyla erişebilir. Böylece içerik izinsiz indirmeye karşı korunur.

### Öne çıkan özellikler

- 🔎 **Gelişmiş katalog:** Arama, çoklu kategori, format ve fiyat filtreleri, 6 farklı sıralama ve sunucu tarafı sayfalama. Filtreler URL'de tutulur, paylaşılabilir.
- 🌍 **Türkçe / İngilizce:** Arayüz, API mesajları, doğrulama hataları, tarihler ve e-postalar seçilen dilde gelir. Dil tercihi hesaba kaydedilir.
- ☁️ **Cloudinary CDN:** Orijinaller gizli saklanır. Önizlemeler imzalı URL'lerle ve tekrarlı filigranla, thumbnail'ler otomatik kırpılıp optimize edilerek sunulur.
- ⚡ **Redis:** Sık sorgulanan kategori, format ve fiyat aralığı verileri önbellekte tutulur, veri değişince önbellek otomatik temizlenir. Sepet oturumları da Redis'te saklanır.
- 📨 **RabbitMQ:** Sipariş işleme ve tüm e-postalar (sipariş özeti, "fotoğrafın satıldı", doğrulama, şifre sıfırlama) kuyruktan **asenkron** işlenir, HTTP isteğini bekletmez.
- 💳 **Stripe:** Güvenli ödeme. Fiyatlar her zaman veritabanından hesaplanır, istemciden gelen fiyata güvenilmez.
- 🔐 **Hesap güvenliği:** E-posta doğrulama, şifre sıfırlama, Sanctum token'ları ve hassas uç noktalarda istek sınırlama (rate limiting).
- ❤️ **Favoriler**, ✏️ **kendi fotoğrafını düzenleme ve silme**, ⭐ **onaylı yorum ve puanlama**.
- 📊 **Modern admin paneli:** Gelir grafiği, en çok satanlar, arama ve filtreli yönetim ekranları.
- ✅ **Testler:** 40 PHPUnit ve 12 Vitest testi, GitHub Actions ile her push'ta otomatik çalışır.

### Kullanılan teknolojiler

| Katman | Teknoloji | Ne için kullanıldı? |
|---|---|---|
| **Backend** | PHP 8.3, **Laravel 11** | REST API, admin paneli, kuyruk job'ları, bildirimler |
| | Laravel Sanctum 4 | API token tabanlı kimlik doğrulama |
| | Eloquent ORM, Form Request, API Resource | Veri modeli, doğrulama, JSON çıktıları |
| | Blade + Bootstrap 5.3 | Admin paneli arayüzü |
| **Frontend** | **React 18**, Vite 5 | Tek sayfa (SPA) mağaza arayüzü |
| | Redux Toolkit 2 + redux-persist | Kullanıcı, sepet ve dil durumu |
| | React Router 6 | Sayfa yönlendirme, filtrelerin URL'de tutulması |
| | Bootstrap 5.3 + özel tasarım sistemi | Responsive, modern arayüz |
| | Axios, React Toastify, Stripe Elements | API istekleri, bildirimler, ödeme formu |
| | Kendi i18n katmanı (`useT`) | Türkçe/İngilizce çeviri |
| **Veritabanı** | **MySQL 8.4** | Kullanıcılar, fotoğraflar, siparişler, yorumlar, favoriler |
| **Önbellek** | **Redis 7** (phpredis) | Katalog önbelleği, sepet oturumları, admin oturumları |
| **Mesaj kuyruğu** | **RabbitMQ 3.13** + laravel-queue-rabbitmq | Asenkron sipariş ve e-posta işleme (`orders`, `notifications` kuyrukları) |
| **Medya ve CDN** | **Cloudinary** (PHP SDK 2.14) | Fotoğraf depolama, CDN ile dağıtım, filigran, thumbnail, imzalı indirme linkleri |
| **Ödeme** | **Stripe** (stripe-php 15, Stripe.js 4) | Kartla ödeme (PaymentIntent) |
| **E-posta** | Laravel Notifications + Mailpit | Yerelde gönderilen e-postaları görüntüleme |
| **Altyapı** | **Docker & Docker Compose**, Nginx 1.27, PHP-FPM | 9 servisin izole ve taşınabilir şekilde çalışması |
| **Test ve CI** | PHPUnit 11, Vitest 2, Testing Library, GitHub Actions | Otomatik test ve sürekli entegrasyon |

### Mimari

```
                 ┌──────────────┐            ┌─────────────────────────────┐
  Tarayıcı ────▶ │ frontend     │◀─ görseller│ Cloudinary (depolama + CDN) │
                 │ React (Vite) │            │ orijinaller gizli           │
                 └──────┬───────┘            │ önizlemeler imzalı+filigran │
                        │ REST /api          └──────────────▲──────────────┘
                        ▼                                   │ yükleme / imzalı URL
                 ┌──────────────────────────────────────┐   │
                 │ web (nginx) ─▶ app (Laravel PHP-FPM) │───┘
                 └──────┬───────────┬──────────┬────────┘
                        ▼           ▼          ▼ mesaj yayınla
                     MySQL       Redis      RabbitMQ ──▶ queue-worker ──▶ Mailpit (SMTP)
                              önbellek/sepet  orders, notifications
```

**Bir siparişin yolculuğu:** Ödeme onaylanır → API siparişleri `pending` olarak kaydeder ve `ProcessOrder` mesajını RabbitMQ'ya gönderir → kullanıcıya hemen yanıt döner → worker siparişi `completed` yapar → alıcıya sipariş e-postası, satıcıya "fotoğrafın satıldı" e-postası `notifications` kuyruğundan gönderilir.

### Kurulum (Docker)

```bash
git clone https://github.com/merthamit/picture-shop-laravel-react.git
cd picture-shop-laravel-react
cp .env.example .env            # Cloudinary ve Stripe test anahtarlarını gir
docker compose up -d --build
docker compose exec app php artisan db:seed --class=DemoSeeder   # isteğe bağlı demo veri
```

Demo seeder 30 kullanıcı, yaklaşık 157 gerçek fotoğraf (Unsplash lisanslı, picsum.photos üzerinden; Cloudinary'ye yüklenir), siparişler, yorumlar ve favoriler oluşturur.

| Servis | Adres | Giriş |
|---|---|---|
| Mağaza (React) | http://localhost:5173 | `buyer@demo.test` / `password`, `seller@demo.test` / `password` |
| Admin paneli | http://localhost:8000 | `admin@laravel.com` / `123456` |
| RabbitMQ yönetim paneli | http://localhost:15672 | `picture_shop` / `secret` |
| Mailpit (gönderilen e-postalar) | http://localhost:8025 | |
| MySQL | `localhost:3307` | `picture_shop` / `secret` |

**`.env` değişkenleri:**

| Değişken | Açıklama |
|---|---|
| `CLOUDINARY_URL` | `cloudinary://<api_key>:<api_secret>@<cloud_name>`. Boş bırakılırsa dosyalar yerel diskte tutulur ve nginx CDN konteynerinden sunulur. |
| `STRIPE_SECRET` | Stripe **test** gizli anahtarı (`sk_test_...`) |
| `VITE_STRIPE_PUBLISHABLE_KEY` | Stripe test açık anahtarı (`pk_test_...`) |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS` | Gerçek e-posta gönderimi (ör. Gmail: `smtp.gmail.com`, `587`, `tls` ve bir **Uygulama Şifresi**). Boş bırakılırsa tüm e-postalar Mailpit'e düşer: http://localhost:8025 |

Test ödemesi için `4242 4242 4242 4242` kartını, ileri bir tarihi ve herhangi bir CVC'yi kullanabilirsin.

### Testler

```bash
# Backend: 40 PHPUnit testi (bellek içi SQLite; sepet testleri için Redis)
docker compose run --rm --no-deps -e RUN_MIGRATIONS=false app php artisan test

# Frontend: Vitest + Testing Library
cd frontend-react && npm test
```

Testlerin kapsamı: katalog filtreleme ve sıralama, yükleme, düzenleme ve silme yetkileri, imzalı indirme, favoriler, sipariş → kuyruk → bildirim akışı, e-posta doğrulama ve şifre sıfırlama, çeviriler, Redis sepeti, başkası adına yorum engeli ve admin erişimi.

---

## 🇬🇧 English

### What is it for?

**Picture Shop** is a **stock photo marketplace**. Photographers sell their work, and designers, bloggers and businesses buy royalty-free images for their projects. Think of it as a small-scale Shutterstock or Adobe Stock.

| Who? | What can they do? |
|---|---|
| 🧑‍🎨 **Photographer (seller)** | Uploads photos and sets their price and category. Tracks sales and earnings, edits or deletes their photos, and gets an e-mail for every sale. |
| 🛒 **Buyer** | Searches and filters the catalog, saves favorites, pays with Stripe and downloads the **full-resolution, watermark-free original**. Can rate and review purchased photos. |
| 🛡️ **Admin** | Follows sales charts. Manages orders, pictures, categories and users, and moderates reviews. |

Photos that haven't been bought are shown **only as watermarked previews**. Only buyers can reach the original file, through a short-lived signed link, which protects the content from unauthorized downloads.

### Key features

- 🔎 **Advanced catalog:** search, multi-category, format and price filters, 6 sort orders and server-side pagination. Filters live in the URL, so a filtered view can be shared.
- 🌍 **Turkish / English:** UI, API messages, validation errors, dates and e-mails follow the selected language, which is saved on the account.
- ☁️ **Cloudinary CDN:** private originals, signed URLs for tiled-watermark previews, and auto-cropped, optimized thumbnails.
- ⚡ **Redis:** caches categories, formats and the price range, and invalidates the cache automatically when data changes. Cart sessions are stored in Redis too.
- 📨 **RabbitMQ:** order processing and every e-mail (receipt, "your photo sold", verification, password reset) run **asynchronously** from queues, so the HTTP request never waits on them.
- 💳 **Stripe:** secure payments. Prices are always computed from the database, never trusted from the client.
- 🔐 **Account security:** e-mail verification, password reset, Sanctum tokens and rate limiting on sensitive endpoints.
- ❤️ **Wishlist**, ✏️ **edit and delete your own photos**, ⭐ **moderated reviews and ratings**.
- 📊 **Modern admin panel:** revenue chart, best sellers, and management screens with search and filters.
- ✅ **Tests:** 40 PHPUnit and 12 Vitest tests, run by GitHub Actions on every push.

### Tech stack

| Layer | Technology | Used for |
|---|---|---|
| **Backend** | PHP 8.3, **Laravel 11** | REST API, admin panel, queue jobs, notifications |
| | Laravel Sanctum 4 | API token authentication |
| | Eloquent ORM, Form Requests, API Resources | Data model, validation, JSON output |
| | Blade + Bootstrap 5.3 | Admin panel UI |
| **Frontend** | **React 18**, Vite 5 | Single-page storefront |
| | Redux Toolkit 2 + redux-persist | User, cart and language state |
| | React Router 6 | Routing, filters kept in the URL |
| | Bootstrap 5.3 + custom design system | Responsive, modern UI |
| | Axios, React Toastify, Stripe Elements | API calls, notifications, payment form |
| | Custom i18n layer (`useT`) | Turkish/English translations |
| **Database** | **MySQL 8.4** | Users, pictures, orders, reviews, favorites |
| **Cache** | **Redis 7** (phpredis) | Catalog cache, cart sessions, admin sessions |
| **Message queue** | **RabbitMQ 3.13** + laravel-queue-rabbitmq | Asynchronous order and e-mail processing (`orders`, `notifications` queues) |
| **Media & CDN** | **Cloudinary** (PHP SDK 2.14) | Storage, CDN delivery, watermarks, thumbnails, signed download links |
| **Payments** | **Stripe** (stripe-php 15, Stripe.js 4) | Card payments (PaymentIntent) |
| **E-mail** | Laravel Notifications + Mailpit | Inspecting sent e-mails locally |
| **Infrastructure** | **Docker & Docker Compose**, Nginx 1.27, PHP-FPM | 9 isolated, portable services |
| **Testing & CI** | PHPUnit 11, Vitest 2, Testing Library, GitHub Actions | Automated tests and continuous integration |

### Architecture

```
                 ┌──────────────┐            ┌────────────────────────────┐
  Browser ─────▶ │ frontend     │◀── images ─│ Cloudinary (storage + CDN) │
                 │ React (Vite) │            │ originals: private         │
                 └──────┬───────┘            │ previews: signed+watermark │
                        │ REST /api          └─────────────▲──────────────┘
                        ▼                                  │ upload / signed URLs
                 ┌──────────────────────────────────────┐  │
                 │ web (nginx) ─▶ app (Laravel PHP-FPM) │──┘
                 └──────┬───────────┬──────────┬────────┘
                        ▼           ▼          ▼ publish
                     MySQL       Redis      RabbitMQ ──▶ queue-worker ──▶ Mailpit (SMTP)
                              cache/cart   orders, notifications
```

**Life of an order:**
1. The payment succeeds.
2. The API stores the orders as `pending`, publishes `ProcessOrder` to RabbitMQ and answers right away.
3. The worker marks the orders `completed`.
4. The buyer's receipt and the seller's "your photo sold" e-mail are sent from the `notifications` queue.

### Getting started (Docker)

```bash
git clone https://github.com/merthamit/picture-shop-laravel-react.git
cd picture-shop-laravel-react
cp .env.example .env            # add your Cloudinary URL and Stripe test keys
docker compose up -d --build
docker compose exec app php artisan db:seed --class=DemoSeeder   # optional demo data
```

The demo seeder creates 30 users, about 157 real photos (Unsplash license, via picsum.photos) uploaded to Cloudinary, plus orders, reviews and favorites.

| Service | URL | Login |
|---|---|---|
| Storefront (React) | http://localhost:5173 | `buyer@demo.test` / `password`, `seller@demo.test` / `password` |
| Admin panel | http://localhost:8000 | `admin@laravel.com` / `123456` |
| RabbitMQ management | http://localhost:15672 | `picture_shop` / `secret` |
| Mailpit (sent e-mails) | http://localhost:8025 | |
| MySQL | `localhost:3307` | `picture_shop` / `secret` |

**`.env` variables:**

| Variable | Description |
|---|---|
| `CLOUDINARY_URL` | `cloudinary://<api_key>:<api_secret>@<cloud_name>`. If empty, files are stored on local disk and served through the nginx CDN container. |
| `STRIPE_SECRET` | Stripe **test** secret key (`sk_test_...`) |
| `VITE_STRIPE_PUBLISHABLE_KEY` | Stripe test publishable key (`pk_test_...`) |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS` | Real e-mail delivery (e.g. Gmail: `smtp.gmail.com`, `587`, `tls` and an **App Password**). If empty, every e-mail is caught by Mailpit: http://localhost:8025 |

For a test payment, use card `4242 4242 4242 4242` with any future date and any CVC.

### Tests

```bash
# Backend: 40 PHPUnit tests (in-memory SQLite; Redis for the cart tests)
docker compose run --rm --no-deps -e RUN_MIGRATIONS=false app php artisan test

# Frontend: Vitest + Testing Library
cd frontend-react && npm test
```

The tests cover catalog filters and sorting, upload, edit and delete permissions, signed downloads, favorites, the order → queue → notification flow, e-mail verification and password reset, translations, the Redis cart, review spoofing and admin access.

---

## 📁 Proje yapısı · Project structure

```
backend-laravel/     Laravel API, admin panel, queue jobs, notifications, tests
  app/Services/      MediaService (Cloudinary), CatalogCache (Redis), CartService (Redis)
  app/Jobs/          ProcessOrder (RabbitMQ)
  lang/{en,tr}/      API & e-mail translations
frontend-react/      React storefront
  src/i18n/          Turkish / English dictionaries
docker/cdn/          nginx edge cache (used when Cloudinary is not configured)
docker-compose.yml   app, queue-worker, web, cdn, frontend, mysql, redis, rabbitmq, mailpit
.github/workflows/   CI: PHPUnit + Vitest + build
```
