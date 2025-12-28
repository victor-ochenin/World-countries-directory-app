FROM php:8.2-fpm

# Установка необходимых инструментов и расширений для MySQL
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mysqli gd zip

    
# Установка opcache для кэширования работы PHP
RUN docker-php-ext-install opcache

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    if [ -d "/root/.symfony5" ]; then mv /root/.symfony5/bin/symfony /usr/local/bin/symfony; fi

# Установка зависимостей Symfony (включая Maker Bundle)
RUN composer require symfony/maker-bundle --dev

# Установка зависимостей приложения
RUN composer install

# Открытие порта для PHP-FPM
EXPOSE 9000
