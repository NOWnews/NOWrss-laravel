FROM php:7.2-fpm
RUN apt-get update && apt-get install -y \
  curl \
  libjpeg62-turbo \
  libjpeg-dev \
  libpng-dev \
  && docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install gd \
  && docker-php-ext-configure bcmath --enable-bcmath \
  && docker-php-ext-install pdo pdo_mysql \
  && docker-php-ext-install mysqli