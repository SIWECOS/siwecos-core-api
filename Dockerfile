FROM php:7.2
RUN apt-get update -y && apt-get install -y openssl zip unzip git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install mysqli pdo_mysql pdo mbstring

WORKDIR /app
COPY . /app
COPY .env.example .env

RUN composer install && php artisan key:generate

#RUN php artisan migrate

#RUN php artisan create:mastertoken

CMD php artisan serve --host=0.0.0.0 --port=8666
EXPOSE 8666
