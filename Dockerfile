FROM php:7.2
RUN apt-get update -y && apt-get install -y openssl zip unzip git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install mysqli pdo_mysql pdo mbstring



ENV PYTHON_VERSION=2.7.13-r1
ENV PY_PIP_VERSION=9.0.1-r1
ENV SUPERVISOR_VERSION=3.3.3

RUN apt-get update && apt-get install python python-pip -y
RUN pip install supervisor==$SUPERVISOR_VERSION

COPY worker.conf /etc/supervisor/supervisord.conf

WORKDIR /app
COPY . /app
COPY .env.example .env

RUN composer install && php artisan key:generate

RUN supervisord --configuration /etc/supervisor/supervisord.conf
#RUN php artisan migrate

#RUN php artisan create:mastertoken

CMD php artisan serve --host=0.0.0.0 --port=8666
EXPOSE 8666
