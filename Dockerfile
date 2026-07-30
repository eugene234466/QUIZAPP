FROM php:8.2-cli

# install curl extension (curl_* functions need this)
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl

WORKDIR /app
COPY . .

EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t public/"]
