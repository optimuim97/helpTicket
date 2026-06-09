#!/usr/bin/env bash
set -e

cd /var/www/html

# Attendre MySQL (au cas où la base démarre en parallèle)
if [ -n "${DB_HOST}" ] && [ "${DB_HOST}" != "127.0.0.1" ] && [ "${DB_HOST}" != "localhost" ]; then
    echo "→ Attente de la base ${DB_HOST}:${DB_PORT:-3306}…"
    for i in $(seq 1 30); do
        if mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT:-3306}" --silent 2>/dev/null; then
            echo "✓ Base disponible."
            break
        fi
        sleep 2
    done
fi

# Générer la clé si elle manque
if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
    echo "→ Génération de APP_KEY…"
    php artisan key:generate --force --no-interaction
fi

# Lien storage pour les uploads
php artisan storage:link --no-interaction 2>/dev/null || true

# Migrations + seed annuaire (si --force activé via env)
echo "→ Migrations…"
php artisan migrate --force --no-interaction

if [ "${RUN_SEEDERS:-0}" = "1" ]; then
    echo "→ Seeders de référence…"
    php artisan db:seed --force --no-interaction
fi

# Caches de production
echo "→ Optimisation des caches…"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
# Inertia/Ziggy : route:cache nécessite que les routes soient prêtes.

# Permissions storage (au cas où un volume est monté)
chown -R www-data:www-data storage bootstrap/cache || true

echo "✓ Application prête."
exec "$@"
