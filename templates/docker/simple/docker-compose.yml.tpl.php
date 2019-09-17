<?php
return <<<EOF
version: '3'

services:
  web:
    image: 'nginx:alpine'
    volumes:
      - ./src:/app
      - ./conf/nginx.conf:/etc/nginx/nginx.conf
      - ./conf/default.conf:/etc/nginx/conf.d/default.conf
    ports:
      - '{$proxyPort}:80'
    restart: always
    depends_on:
      - php-fpm
  php-fpm:
    image: 'php-7.3-fpm-alpine-image'
    build: ./php
    ports:
      - '9000:9000'
    volumes:
      - ./src:/app
      - ./conf/php.ini:/usr/local/etc/php/php.ini

EOF;
