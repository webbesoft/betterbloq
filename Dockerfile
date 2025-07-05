ARG BUN_VERSION="latest"
ARG APP_ENV

FROM serversideup/php:8.4-fpm-nginx AS base

# Switch to root so we can do root things
USER root

# Install the exif extension with root permissions
RUN install-php-extensions exif intl bcmath gd

# Install JavaScript dependencies
ARG NODE_VERSION=20.18.0
ENV PATH=/usr/local/node/bin:$PATH
RUN curl -sL https://github.com/nodenv/node-build/archive/master.tar.gz | tar xz -C /tmp/ && \
    /tmp/node-build-master/bin/node-build "${NODE_VERSION}" /usr/local/node && \
    # Corepack will install yarn automatically according to my package.json
    corepack enable && \
    rm -rf /tmp/node-build-master

# Drop back to our unprivileged user
USER www-data

###########################################
# Build frontend assets with Bun
###########################################

# FROM oven/bun:${BUN_VERSION} AS build

# ARG APP_ENV
# ARG APP_URL
# ARG VITE_APP_URL

# ENV ROOT=/var/www/html \
#     APP_ENV=${APP_ENV} \
#     NODE_ENV=${APP_ENV:-production} \
#     VITE_APP_URL=${VITE_APP_URL}

# WORKDIR ${ROOT}

# COPY --link package.json bun.lock* ./

# RUN bun install --frozen-lockfile

# COPY --link . .

# RUN bun run build

###########################################
# Final stage
###########################################
FROM base

# These are environments variables from https://serversideup.net/open-source/docker-php/docs/reference/environment-variable-specification
# Disable SSL on NGINX level, Kamal-proxy will handle that for us.
ENV SSL_MODE="off"
# See: https://serversideup.net/open-source/docker-php/docs/laravel/laravel-automations
ENV AUTORUN_ENABLED="true"
ENV PHP_OPCACHE_ENABLE="1"
ENV HEALTHCHECK_PATH="/up"
ARG APP_ENV
ARG APP_URL
ARG VITE_APP_URL

ENV ROOT=/var/www/html \
    APP_ENV=${APP_ENV} \
    NODE_ENV=${APP_ENV:-production} \
    VITE_APP_URL=${VITE_APP_URL}

# Copy the app files...
COPY --chown=www-data:www-data . /var/www/html

# Re-run install, but now with scripts and optimizing the autoloader (should be faster)...
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN npm ci && \
    npm run build && \
    rm -rf node_modules