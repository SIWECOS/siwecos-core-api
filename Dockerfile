FROM php:7.2-alpine
RUN apk update && apk add openssl zip unzip git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install mysqli pdo_mysql pdo mbstring



ENV PYTHON_VERSION=2.7.13-r1
ENV PY_PIP_VERSION=9.0.1-r1
ENV SUPERVISOR_VERSION=3.3.3

RUN apk update && apk add python py-pip
RUN pip install supervisor==$SUPERVISOR_VERSION

COPY php.ini /usr/local/etc/php/
COPY worker.conf /etc/supervisor/supervisord.conf

WORKDIR /app
COPY . /app
COPY .env.example .env

RUN composer install && php artisan key:generate

CMD supervisord --nodaemon --configuration /etc/supervisor/supervisord.conf
#RUN php artisan migrate

#RUN php artisan create:mastertoken
EXPOSE 8666
