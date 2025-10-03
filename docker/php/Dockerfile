FROM php:8.3-fpm-alpine

# Pacotes e extensões típicas do Laravel
RUN apk add --no-cache git curl unzip libzip-dev oniguruma-dev icu-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && docker-php-ext-enable opcache

RUN apk add --no-cache tzdata \
    && cp /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime \
    && echo "America/Sao_Paulo" > /etc/timezone

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
