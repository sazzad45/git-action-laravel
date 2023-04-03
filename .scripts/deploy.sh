#!/bin/bash
set -e

echo "Deployment started ..."

# Enter maintenance mode or return true
# if already is in maintenance mode
(php artisan down) || true

# Pull the latest version of the app
#git fetch
#git stash
#git pull --rebase origin main
echo dev
pushd /var/www/html/git-action-laravel/
sudo git checkout .
sudo git pull origin main
sudo composer install 
sudo chmod -R 777 storage
sudo chmod -R 777 bootstrap/cache
popd
sudo chown -Rf nginx:fpay /var/www/html/git-action-laravel/
sudo chmod -Rf 770 /var/www/html/git-action-laravel/
sudo chmod -Rf g+s /var/www/html/git-action-laravel/
sudo systemctl reload php-fpm.service
sudo nginx -s reload

# Install composer dependencies
#composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear the old cache
php artisan clear-compiled

# Recreate cache
#php artisan optimize

# Compile npm assets
#npm run prod

# Run database migrations
#php artisan migrate --force

# Exit maintenance mode
php artisan up
echo "Deployment finished!"