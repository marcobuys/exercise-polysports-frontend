FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        gnupg \
        libzip-dev \
        libicu-dev \
        libsqlite3-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_sqlite zip intl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000 5173

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
