#!/bin/bash

# printf "-----Set www-data uid to \$uid-----\n"
# docker-compose exec php usermod -u $UID www-data

# printf "-----Create .env.locla.php-----\n"
# docker-compose exec -u www-data php cp dev/.env.local.php ./.env.local.php

# printf "-----Composer install-----\n"
# docker-compose exec -u www-data php composer install

# printf "-----Execute migrations-----\n"
# docker-compose exec -u www-data php bin/console doctrine:migrations:migrate --no-interaction

# printf '-----Execute init insert-----\n'
docker-compose exec -it db mysql --password=root < dev/init_insert.sql