FROM php:7.2-apache

# Settings
ENV PYTHON_VERSION=2.7.13-r1
ENV PY_PIP_VERSION=9.0.1-r1
ENV SUPERVISOR_VERSION=3.3.3
ENV APP_HOME /var/www/html

# Install all dependencies
RUN apt-get update -y \
    && apt-get install -y openssl zip unzip cron git vim python python-pip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && docker-php-ext-install mysqli pdo_mysql pdo mbstring \
    && pip install supervisor==$SUPERVISOR_VERSION

# Copy configuration files
COPY php.ini /usr/local/etc/php/
COPY worker.conf /etc/supervisor/supervisord.conf

# Change uid and gid of apache to docker user uid/gid
# Change the web_root to laravel /var/www/html/public folder
# Enable apache module rewrite
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data \
    && sed -i -e "s/html/html\/public/g" /etc/apache2/sites-enabled/000-default.conf \
    && a2enmod rewrite

# Copy source files and run composer
COPY . $APP_HOME

# Install all PHP dependencies and change ownership of our applications
RUN composer install --no-interaction \
    && chown -R www-data:www-data $APP_HOME

WORKDIR /var/www/html

COPY .env.example .env

RUN ln -sf /proc/1/fd/1 ./scheduleOutputToStdout

CMD php artisan key:generate && supervisord --nodaemon --configuration /etc/supervisor/supervisord.conf

EXPOSE 80
