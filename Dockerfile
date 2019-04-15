FROM siwecos/php-apache:7.2

LABEL maintainer="Sascha Brendel <mail@lednerb.eu>"

# Copy application
COPY . .
COPY .env.example .env

# Install all PHP dependencies and change ownership of our applications
RUN composer install --no-dev --no-interaction \
    && chown -R www-data:www-data .

EXPOSE 80
