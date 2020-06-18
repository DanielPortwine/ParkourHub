FROM php:7.4-fpm

WORKDIR /var/www

RUN apt-get update; apt-get install -y \
        libcurl4-gnutls-dev \
        libsqlite3-0 \
        libxml2-dev \
        libz-dev \
        libicu-dev \
        curl \
        default-mysql-client \
        cron \
        libonig-dev \
        libzip-dev \
        jpegoptim \
        optipng \
        pngquant

COPY --from=composer:1.9.3 /usr/bin/composer /usr/bin/composer

## PHP Extensions
RUN docker-php-ext-install curl
RUN docker-php-ext-install json
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install xml
RUN docker-php-ext-install zip
RUN docker-php-ext-install bcmath
RUN echo "max_file_uploads=5" >> /usr/local/etc/php/conf.d/docker-php-ext-max_file_uploads.ini
RUN echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/docker-php-ext-post_max_size.ini
RUN echo "upload_max_filesize=100M" >> /usr/local/etc/php/conf.d/docker-php-ext-upload_max_filesize.ini
