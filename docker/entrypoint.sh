#!/bin/bash
set -e

echo "waiting for database..."
until php -r "
try {
    new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
  echo "Database not ready, waiting..."
  sleep 2
done
echo "Database is ready."

if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

php artisan storage:link || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

exec apache2-foreground