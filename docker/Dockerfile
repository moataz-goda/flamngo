# ---------- مرحلة 1: تجهيز مكتبات كومبوزر ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ---------- مرحلة 2: بناء الفرونت إند (Vite + Tailwind) ----------
# المشروع محتاج Node 20.19+ لأنه شغال بـ Vite 8
FROM node:22-slim AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install --ignore-scripts
COPY resources/ resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# ---------- مرحلة 3: الصورة النهائية (PHP + أباتشي) ----------
FROM php:8.3-apache

# تفعيل mod_rewrite عشان راوتس لارافيل تشتغل
RUN a2enmod rewrite

# تثبيت الإضافات المطلوبة لـ لارافيل + MySQL
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    git \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && rm -rf /var/lib/apt/lists/*

# توجيه الدوكيومنت روت لمجلد public بتاع لارافيل
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# نسخ المشروع
COPY . .

# نسخ مكتبات كومبوزر من المرحلة الأولى
COPY --from=vendor /app/vendor ./vendor

# نسخ ملفات الفرونت إند المبنية (public/build) من مرحلة node
COPY --from=frontend /app/public/build ./public/build

# صلاحيات مجلدات التخزين والكاش
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# سكريبت البدء: تشغيل المايجريشن + كاش الإعدادات + تشغيل أباتشي
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
