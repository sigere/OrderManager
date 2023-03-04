#!/bin/bash

echo "----- Set www-data uid to \$uid -----"
docker-compose exec php usermod -u $UID www-data

echo "----- Set ownership to www-data -----"
docker-compose exec php chown www-data:www-data /var/www/OrderManager -R

echo "----- Create .composer directory -----"
docker-compose exec php mkdir -p /var/www/.composer
docker-compose exec php chown www-data:www-data /var/www/.composer

echo "----- Create .env -----"
docker-compose exec -u www-data php cp ./.env.example ./.env

echo "----- Composer install -----"
docker-compose exec -u www-data php composer install

echo "----- Execute migrations -----"
sleep 5
docker-compose exec -u www-data php bin/console doctrine:migrations:migrate --no-interaction

echo '----- Install Sample Data -----'
docker-compose exec -u www-data php bin/console app:installSampleData