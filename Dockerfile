#
FROM composer:2.2.5 as composer

# 
FROM php:8.1.2-cli-bullseye

WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

COPY . /app

RUN apt update && \
  apt-get -y install git libzip-dev

RUN pecl install ast && \
  docker-php-ext-enable ast && \
  docker-php-ext-install zip

