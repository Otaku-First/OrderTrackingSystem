FROM php:8.3-fpm

# Встановлення необхідних системних пакетів
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    bash \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql pcntl

# Встановлення Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Встановлення Redis
RUN pecl install redis && docker-php-ext-enable redis

# Встановлення розширення opcache (опційно)
RUN docker-php-ext-install opcache && docker-php-ext-enable opcache

# Копіювання додатку
WORKDIR /var/www/laravel
