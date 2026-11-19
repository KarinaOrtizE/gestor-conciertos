#!/bin/sh
# Arranque del contenedor: prepara Laravel y abre Apache en el puerto que asigna Render.
set -e
cd /var/www/html

PUERTO="${PORT:-8080}"
sed -ri "s/^Listen [0-9]+/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

# Render entrega la URL pública en RENDER_EXTERNAL_URL.
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost:${PUERTO}}}"

# Con DB_URL se usa PostgreSQL; sin ella, una base SQLite que se pierde en cada reinicio.
if [ -n "$DB_URL" ]; then
    export DB_CONNECTION=pgsql
else
    echo "Aviso: DB_URL no está definida, se usa SQLite temporal." >&2
    export DB_CONNECTION=sqlite
    touch database/database.sqlite
fi

php artisan migrate --force
php artisan db:sembrar-si-vacia

php artisan storage:link --force
php artisan optimize
chown -R www-data:www-data storage bootstrap/cache database

exec apache2-foreground
