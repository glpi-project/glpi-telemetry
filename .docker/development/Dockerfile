FROM composer:latest AS composer

FROM php:8.3-apache-bookworm

# Workaround to make apache use same UID/GID as host user.
ARG HOST_GROUP_ID=1000
RUN groupmod --gid ${HOST_GROUP_ID} www-data
ARG HOST_USER_ID=1000
RUN usermod --uid ${HOST_USER_ID} www-data

# Make www-data user home persistent for cache purpose.
RUN mkdir --parents /home/www-data \
  && chown www-data:www-data /home/www-data \
  && usermod --home /home/www-data www-data
VOLUME /home/www-data

RUN apt-get update \
  \
  # Install APCU PHP extension.
  && pecl install apcu \
  && docker-php-ext-enable apcu \
  && echo "apc.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini \
  \
  # Install gettext PHP extension.
  && docker-php-ext-install gettext \
  \
  # Install intl PHP extension.
  && apt-get install --assume-yes --no-install-recommends --quiet libicu-dev \
  && docker-php-ext-configure intl \
  && docker-php-ext-install intl \
  \
  # Install opcache PHP extension.
  && docker-php-ext-install opcache \
  \
  # Install PDO MySQL PHP extension.
  && docker-php-ext-install pdo pdo_mysql \
  \
  # Install zip PHP extension (for `php-webdriver/webdriver`).
  && apt-get install --assume-yes --no-install-recommends --quiet libzip-dev zip \
  && docker-php-ext-install zip \
  \
  # Install nodejs and npm.
  && apt-get install --assume-yes --no-install-recommends --quiet gnupg \
  && mkdir -p /etc/apt/keyrings \
  && curl --fail --silent --show-error --location https://deb.nodesource.com/setup_20.x | bash -E \
  && apt-get install --assume-yes --no-install-recommends --quiet nodejs \
  \
  # Install git and zip used by composer when fetching dependencies.
  && apt-get install --assume-yes --no-install-recommends --quiet git unzip \
  \
  # Install browser driver used for E2E tests.
  && apt-get install --assume-yes --no-install-recommends --quiet chromium-driver \
  \
  # Clean sources list
  && rm -rf /var/lib/apt/lists/*

# Copy composer binary
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy files to container.
COPY ./files/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# Define application path as volume and working dir
VOLUME /var/www/telemetry.glpi-project.org
WORKDIR /var/www/telemetry.glpi-project.org
