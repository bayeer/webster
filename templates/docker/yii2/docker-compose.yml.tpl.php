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
      - database
  php-fpm:
    image: 'php-7.3-fpm-alpine-image1'
    build: ./php
    ports:
      - '9000:9000'
    volumes:
      - ./src:/app
      - ./conf/php.ini:/usr/local/etc/php/php.ini
  database:
    image: 'mysql:5.6'
    restart: always
    command: --default-authentication-plugin=mysql_native_password --character-set-server={$charset} --collation-server={$charset}_general_ci
    environment:
      - MYSQL_DATABASE={$dbName}
      - MYSQL_ROOT_PASSWORD={$dbRootPassw}
      - MYSQL_USER={$dbName}
      - MYSQL_PASSWORD={$dbPassw}
    ports:
      - '3306:3306'
    volumes:
      - {$dbDir}{$dbName}:/var/lib/mysql

EOF;
