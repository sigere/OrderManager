#!/bin/bash

docker-compose exec -u www-data php \
  ./vendor/bin/phpcs --config-set colors 1

docker-compose exec -u www-data php \
  ./vendor/bin/phpcs \
  --extensions=php \
  --standard=./dev/phpcs/ruleset.xml \
  ./src  -s