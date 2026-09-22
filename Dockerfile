FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev libpq-dev \
    && docker-php-ext-install -j$(nproc) curl mbstring pdo_pgsql \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-travel-guide.ini
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader \
    && mkdir -p public/uploads/profiles public/uploads/posts \
    && chown -R www-data:www-data public/uploads \
    && chmod +x docker/entrypoint.sh

COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf.template
COPY docker/ports.conf.template /etc/apache2/ports.conf.template

EXPOSE 10000
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["apache2-foreground"]
