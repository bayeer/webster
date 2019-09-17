<?php
return $dockerfile = <<<'EOF'
FROM php:7.3-fpm-alpine
RUN apk add --no-cache freetype-dev libjpeg-turbo-dev libpng-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd pdo_mysql

EOF;
