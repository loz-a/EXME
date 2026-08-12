    
ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libxml2-dev \
    libxslt1-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN set -eux; \
    install-php-extensions \
    gd \
    intl \
    pdo pdo_mysql mysqli \
    bcmath \
    opcache \
    soap \
    xsl \
    zip \
    sockets \
    xdebug

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y --force-yes --no-install-recommends \
    curl \
    && rm -rf /var/lib/apt/lists/* 

#INSTALL COMPOSER
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Optional: if you want to install composer deps during build (recommended for prod)
# WORKDIR /app
# COPY composer.json composer.lock ./
# RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-progress --no-interaction

ARG USER_ID
ARG GROUP_ID
ARG CONTAINER_SRC_PATH
ARG APACHE_SERVER_NAME
ARG APACHE_SERVER_ALIAS

# https://jtreminio.com/blog/running-docker-containers-as-current-host-user/
RUN if [ ${USER_ID:-0} -ne 0 ] && [ ${GROUP_ID:-0} -ne 0 ]; then \
    userdel -f www-data 2>/dev/null || true; \
    groupdel www-data 2>/dev/null || true; \
    groupadd -g ${GROUP_ID} www-data; \
    useradd -l -u ${USER_ID} -g www-data -m -d /home/www-data www-data; \
    install -d -m 0755 -o www-data -g www-data /home/www-data; \
    chown -R ${USER_ID}:${GROUP_ID} /home/www-data; \
    fi

# generate keys before 
# @link: https://github.com/FiloSottile/mkcert
# mkcert exme.devel 127.0.0.1 ::1
# mkcert -install
#
# SETUP SSL certificate
# RUN mkdir -p /etc/apache2/ssl \
#     && chown -R ${USER_ID}:${GROUP_ID} /etc/apache2/ssl
RUN mkdir -p /etc/apache2/ssl
COPY ./app/apache/ssl/*.pem /etc/apache2/ssl/
RUN chmod 644 /etc/apache2/ssl/*.pem
# SSL SETUPED

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ./app/php/conf.d/*.ini /usr/local/etc/php/conf.d/
COPY ./app/apache/sites-available/000-default.conf /etc/apache2/sites-available/

RUN sed -i "s#error_reporting = .*#error_reporting = E_ALL \& ~E_NOTICE \& ~E_STRICT \& ~E_DEPRECATED#g" "$PHP_INI_DIR/php.ini" \
    && sed -i "s#upload_max_filesize = .*#upload_max_filesize = 50M#g" "$PHP_INI_DIR/php.ini" \
    && sed -i "s#max_execution_time = .*#max_execution_time = 600#g" "$PHP_INI_DIR/php.ini" \
    && echo "export SERVER_ALIAS=${APACHE_SERVER_ALIAS}" >> /etc/apache2/envvars \
    && echo "export SERVER_NAME=${APACHE_SERVER_NAME}" >> /etc/apache2/envvars \
    && echo "export DOC_ROOT=${CONTAINER_SRC_PATH}public" >> /etc/apache2/envvars \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite ssl \
    && a2ensite 000-default.conf

# https://gist.github.com/stefanbc/9956ed211cd32571e73f
# RUN mkdir ${CONTAINER_SRC_PATH} && \
#       chown -R www-data:www-data ${CONTAINER_SRC_PATH}
#     && find ${CONTAINER_SRC_PATH} -type f -exec chmod 664 {} + \  
#     && find ${CONTAINER_SRC_PATH} -type d -exec chmod 775 {} + \  
#     && find ${CONTAINER_SRC_PATH} -type d -exec chmod g+s {} +  
   # last line ensures the directory is writable by Apache and anyone in the webmasters group

WORKDIR "${CONTAINER_SRC_PATH}"

RUN chown ${USER_ID}:${GROUP_ID} ${CONTAINER_SRC_PATH}

USER www-data
