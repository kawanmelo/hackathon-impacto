FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libxml2-dev \
    unzip \
    curl \
    git \
    && docker-php-ext-install pdo pdo_pgsql zip xml

WORKDIR /var/www

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install
COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 9000

# Comando padrão
CMD ["php-fpm"]
