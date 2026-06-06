FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        default-mysql-client \
        git \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        bcmath \
        dom \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        xml \
        xmlwriter \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-bookworm /usr/local /usr/local

WORKDIR /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/99-beach-restaurant.ini
COPY docker/entrypoint.sh /usr/local/bin/beach-restaurant-entrypoint

RUN chmod +x /usr/local/bin/beach-restaurant-entrypoint

EXPOSE 8000 5173

ENTRYPOINT ["beach-restaurant-entrypoint"]
CMD ["start"]
