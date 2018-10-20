FROM php:7.2-apache
RUN apt-get update -y && apt-get install openssl zip unzip git -y
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install mysqli pdo_mysql pdo mbstring

ENV PYTHON_VERSION=2.7.13-r1
ENV PY_PIP_VERSION=9.0.1-r1
ENV SUPERVISOR_VERSION=3.3.3

RUN apt-get update -y && apt-get install -y python python-pip
RUN pip install supervisor==$SUPERVISOR_VERSION

COPY php.ini /usr/local/etc/php/
COPY worker.conf /etc/supervisor/supervisord.conf

#set our application folder as an environment variable
ENV APP_HOME /var/www/html

#change uid and gid of apache to docker user uid/gid
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

#change the web_root to laravel /var/www/html/public folder
RUN sed -i -e "s/html/html\/public/g" /etc/apache2/sites-enabled/000-default.conf

# enable apache module rewrite
RUN a2enmod rewrite

#copy source files and run composer
COPY . $APP_HOME

# install all PHP dependencies
RUN composer install --no-interaction

#change ownership of our applications
RUN chown -R www-data:www-data $APP_HOME

WORKDIR /var/www/html

COPY .env.example .env

CMD supervisord --nodaemon --configuration /etc/supervisor/supervisord.conf
#RUN php artisan migrate

EXPOSE 80
