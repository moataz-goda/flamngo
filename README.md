# Flamingo Gift Shop

Multi-tenant Arabic RTL gift platform for the Egyptian market (Laravel + repository pattern).

Shops:
- `https://flamingo.test` — Flamingo (purple theme)
- `https://bubbles.test` — Bubbles (premium rose theme)

Each shop has isolated catalogue, reservations, cart, and admin users.

Gift catalogue (same categories for both shops): ساعات يد / Wristwatches، محافظ / Wallets، عطور / Perfume، نظارات شمس / Sunglasses، حقائب / Bags، أساور / Bracelets، أحزمة / Belts — with real stock photos under `public/images/stock/`.

Language: AR/EN toggle in storefront header and admin. Catalog fields are bilingual (`name` + `name_en`, etc.).

After pulling, reseed (downloads/copies real product photos):

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

Currency: EGP (ج.م)  
Phones: Egyptian mobiles (010 / 011 / 012 / 015)

Admins (owners):
- Flamingo: `admin@flamingo.shop` / `password`
- Bubbles: `admin@bubbles.shop` / `password`

Staff (catalog/reservations only — no shop settings):
- Flamingo: `staff@flamingo.shop` / `password`
- Bubbles: `staff@bubbles.shop` / `password`

Local/ngrok shop switch: `?shop=flamingo` or `?shop=bubbles` (remembered in session).

Notable features: product attributes + variants, wishlist, banners CMS, CSV import/export, sales reports, activity log, AR/EN locale, about/contact pages.

## المتطلبات

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8

## التثبيت

```bash
composer install
cp .env.example .env
php artisan key:generate
```

عدّل إعدادات MySQL في `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=flamingo
DB_USERNAME=flamingo
DB_PASSWORD=secret
```

ثم:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Inside Homestead, run `migrate:fresh --seed` from the project folder after pulling these changes.

- المتجر: http://127.0.0.1:8000
- لوحة التحكم: http://127.0.0.1:8000/admin/login

### بيانات المشرف الافتراضية

- البريد: `admin@flamingo.shop`
- كلمة المرور: `password`

## البنية

- `app/Contracts/Repositories` — واجهات المستودعات
- `app/Repositories/Eloquent` — التنفيذ على Eloquent
- `app/Services` — منطق الأعمال (سلة، حجز، مخزون)
- `app/Http/Controllers/Storefront` — واجهة المتجر
- `app/Http/Controllers/Admin` — لوحة التحكم

الحجز يبدأ بحالة `pending` ويخصم من `reserved_quantity`. عند القبول يخصم من المخزون الفعلي، وعند الرفض يحرر الكمية المحجوزة.
