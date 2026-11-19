#!/bin/sh
# Arranque del contenedor: prepara Laravel y abre Apache en el puerto que asigna Render.
set -e
cd /var/www/html

PUERTO="${PORT:-8080}"
sed -ri "s/^Listen [0-9]+/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

# Render entrega la URL pública en RENDER_EXTERNAL_URL.
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost:${PUERTO}}}"

# El disco del plan gratuito se borra en cada reinicio: la base SQLite se crea de nuevo con los datos de prueba.
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    php artisan migrate --force
    php artisan db:seed --force
else
    php artisan migrate --force
fi

php artisan storage:link --force
php artisan optimize
chown -R www-data:www-data storage bootstrap/cache database

exec apache2-foreground
