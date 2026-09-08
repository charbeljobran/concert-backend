#!/bin/sh
set -e

# Make sure the sqlite database file exists (it's gitignored, so it won't
# exist in a fresh container).
touch database/database.sqlite

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

exec php artisan serve --host=0.0.0.0 --port=8080
