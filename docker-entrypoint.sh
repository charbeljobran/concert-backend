#!/bin/sh
set -e

# Generate an app key if one wasn't supplied via env vars.
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Migrations are safe to re-run on every deploy/restart.
php artisan migrate --force

# Only seed the initial tables/prices once, so redeploys don't duplicate
# or reset real reservation data.
TABLE_COUNT=$(php artisan tinker --execute="echo \App\Models\Table::count();" 2>/dev/null | tail -n 1)
if [ "$TABLE_COUNT" = "0" ]; then
    php artisan db:seed --force
fi

# Render assigns the port to listen on via $PORT at runtime — it's not
# known at build time, so it must be read here rather than hardcoded.
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
