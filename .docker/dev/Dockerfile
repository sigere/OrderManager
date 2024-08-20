FROM php:8.3-cli

RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer --version=2.7.7

RUN curl -sS https://get.symfony.com/cli/installer | bash &&  \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -

RUN apt update

RUN apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip

RUN pecl install apcu xdebug redis
RUN docker-php-ext-install intl opcache pdo pdo_mysql zip
RUN docker-php-ext-configure zip
RUN docker-php-ext-enable apcu xdebug redis

RUN apt install -y \
    nodejs yarn  \
    nano  \
    default-mysql-client \
    iputils-ping \
    bash-completion

RUN useradd -m -s /bin/bash -u 1000 app

RUN chown app:app /var/www -R

WORKDIR /var/www/OrderManager

USER app
