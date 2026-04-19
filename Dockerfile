FROM php:8.4-cli-alpine

RUN apk add --no-cache python3 py3-pip \
    && docker-php-ext-install pdo_mysql

WORKDIR /app

COPY . /app

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
