#!/bin/bash

echo "----- Set www-data uid to \$uid -----"
docker-compose exec php usermod -u $UID www-data

echo "----- Set ownership to www-data -----"
docker-compose exec php chown www-data:www-data /var/www/OrderManager

echo "----- Create .composer directory -----"
docker-compose exec php mkdir -p /var/www/.composer
docker-compose exec php chown www-data:www-data /var/www/.composer