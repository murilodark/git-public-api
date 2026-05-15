FROM php:8.3-fpm

ARG user=gitpublicapi
ARG uid=1000
ARG XDEBUG_MODE=off

ENV XDEBUG_MODE=${XDEBUG_MODE}

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    sockets \
    zip \
    opcache \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && if [ "$XDEBUG_MODE" != "off" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug ; \
    fi \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u ${uid} -d /home/${user} ${user} \
    && mkdir -p /home/${user}/.composer \
    && chown -R ${user}:${user} /home/${user}

RUN mkdir -p /var/www/api \
    && chown -R ${user}:www-data /var/www \
    && chmod -R 775 /var/www

WORKDIR /var/www/api

COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

USER ${user}