#!/bin/bash
set -e

echo "Deployment started ..."

# Enter maintenance mode or return true
# if already is in maintenance mode
(php artisan down) || true

# Pull the latest version of the app
git stash
git pull --rebase origin main
git pull origin main

# Install composer dependencies
composer install --ignore-platform-reqs
# Clear the old cache
php artisan clear-compiled

# Recreate cache
php artisan optimize

# Compile npm assets
#npm run prod

# Run database migrations
#php artisan migrate --force

# Exit maintenance mode
php artisan up
echo "Deployment finished!"
