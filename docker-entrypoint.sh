#!/bin/sh
set -e

# Generate an app key if one wasn't supplied via env vars.
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Migrations are safe to re-run on every deploy/restart.
php artisan migrate --force

# Every seeder uses updateOrCreate/firstOrCreate, so this is safe to run
# on every boot — it won't duplicate rows or touch real reservation data,
# and it will finish filling in any tables/prices left over from a
# previous interrupted seed.
php artisan db:seed --force

# Render assigns the port to listen on via $PORT at runtime — it's not
# known at build time, so it must be read here rather than hardcoded.
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
