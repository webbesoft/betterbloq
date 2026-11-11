ARG BUN_VERSION=1.3.14
FROM oven/bun:${BUN_VERSION} AS bun

FROM serversideup/php:8.4-fpm-nginx
USER root
RUN install-php-extensions exif intl bcmath gd
COPY --from=bun /usr/local/bin/bun /usr/local/bin/bun

USER www-data
WORKDIR /var/www/html

ENV SSL_MODE="off" \
    AUTORUN_ENABLED="true" \
    PHP_OPCACHE_ENABLE="1" \
    HEALTHCHECK_PATH="/up"
ARG APP_ENV=production
ARG VITE_APP_NAME=BetterBloq
ARG VITE_APP_URL
ENV APP_ENV=${APP_ENV} \
    VITE_APP_NAME=${VITE_APP_NAME} \
    VITE_APP_URL=${VITE_APP_URL}

COPY --chown=www-data:www-data . /var/www/html
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
# The tracked Bun lockfile is the dependency source of truth. Do not resolve
# a different dependency tree with npm or ship development packages at runtime.
RUN bun install --frozen-lockfile --cache-dir /tmp/bun-cache && \
    bun --bun run build && \
    rm -rf node_modules /tmp/bun-cache
