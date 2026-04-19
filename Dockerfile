FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    && docker-php-ext-install pdo_mysql

WORKDIR /app

COPY . /app

EXPOSE 8080

CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT:-8080} -t public 2>&1"]
