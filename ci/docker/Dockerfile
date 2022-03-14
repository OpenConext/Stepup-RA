FROM php:7.2

SHELL ["/bin/bash", "-c"]

ARG NPM_UID=1000
ARG NPM_GID=1000

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    python \
    zip \
    libpng-dev \
    && docker-php-ext-install pdo_mysql exif gd \
    ## APCu
    && pecl install apcu \
    && pecl install apcu_bc-1.0.3 \
    && docker-php-ext-enable apcu --ini-name 10-docker-php-ext-apcu.ini \
    && docker-php-ext-enable apc --ini-name 20-docker-php-ext-apc.ini

ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 10

# Install nvm
# For reference look at: https://github.com/creationix/nvm#install-script
RUN mkdir -p /usr/local/nvm
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.37.2/install.sh | bash

# Install node and npm
RUN source $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

# Composer
COPY --from=composer:1 /usr/bin/composer /usr/local/bin/composer

# Fix npm
RUN mkdir /.npm && chown -R "${NPM_UID}:${NPM_GID}" "/.npm"
RUN mkdir /.config && chown -R "${NPM_UID}:${NPM_GID}" "/.config"

# Cleanup
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/www

