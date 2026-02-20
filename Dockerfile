# syntax=docker/dockerfile:1

# Source image (update php version when needed)
FROM php:8.3.12-apache as final

# We're going to use this path multile times. So save it in a variable.
ARG UPLOADS_INI="/usr/local/etc/php/conf.d/uploads.ini"

# enable Rewrites and setup mysqli, pdo, and xdebug
RUN a2enmod rewrite \
    && apt-get update && apt-get install -y libmariadb-dev \
    #
    # Install and enable mysqli and pdo_mysql extensions
    && docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql \
    #
    # increase upload sizes
    && echo "upload_max_filesize = 128M" > ${UPLOADS_INI} \
    && echo "post_max_size = 128M" >> ${UPLOADS_INI} \
    #
    # Install and enable xdebug. Check PHP version compatibility! (https://xdebug.org/docs/compat#versions)
    && pecl install xdebug-3.4.1 \
    && docker-php-ext-enable xdebug

USER www-data
