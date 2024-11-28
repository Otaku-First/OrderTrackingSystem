FROM composer:latest

WORKDIR /var/www/laravel

ENTRYPOINT ["composer", "install", "--ignore-platform-reqs"]
