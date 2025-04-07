# Stage 1: Build environment, Composer dependencies, and Static Assets
FROM php:8.4-fpm AS builder

ARG NODE_MAJOR=23

# Install system dependencies, PHP extensions, Git, and Node.js/NPM
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    git \
    libmariadb-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    # Node.js setup
    && curl -fsSL https://deb.nodesource.com/setup_${NODE_MAJOR}.x | bash - \
    && apt-get install -y nodejs \
    # Install PHP extensions
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    opcache \
    intl \
    zip \
    bcmath \
    soap \
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Clean up
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Set the working directory inside the container
WORKDIR /var/www

# Copy the entire Laravel application code into the container
# Assumes you have checked out the correct branch (staging or main) BEFORE building!
COPY . /var/www

# Install Composer and dependencies (production-optimized)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# Install Node.js dependencies and build assets for production
RUN npm ci && npm run build

# Stage 2: Production environment
FROM php:8.4-fpm as production

# Accept build arguments for environment specific settings
ARG USER=www-data
ARG UID=1000
ARG GID=1000
ARG FPM_LISTEN_PORT=9000

# Install only runtime libraries needed in production
# libfcgi-bin and procps are required for the php-fpm-healthcheck script
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libzip-dev \
    libfcgi-bin \
    procps \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Download and install php-fpm health check script
RUN curl -o /usr/local/bin/php-fpm-healthcheck \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# Copy the initialization script (if you have one, adjust path if needed)
# COPY ./docker/production/php-fpm/entrypoint.sh /usr/local/bin/entrypoint.sh
# RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy the initial storage structure (optional, useful for directory setup)
# COPY ./storage /var/www/storage-init

# Copy PHP extensions and libraries from the builder stage
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /usr/local/bin/docker-php-ext-* /usr/local/bin/

# Use the recommended production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Enable PHP-FPM status page
RUN sed -i '/\[www\]/a pm.status_path = /status' /usr/local/etc/php-fpm.d/zz-docker.conf

# Configure PHP-FPM to listen on the specified port
RUN sed -i "s|listen = 127.0.0.1:9000|listen = 0.0.0.0:${FPM_LISTEN_PORT}|" /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy the application code and built assets from the build stage
COPY --from=builder /var/www /var/www

# Create a non-root user to run the application
RUN groupadd -g $GID -o ${USER} && useradd -m -u $UID -g $GID -o -s /bin/bash ${USER}

# Set working directory
WORKDIR /var/www

# Ensure correct permissions (adjust if entrypoint script handles this)
# RUN chown -R ${USER}:${USER} /var/www  # Run chown before switching user if needed

# Switch to the non-privileged user to run the application
USER ${USER}

# Change the default command to run the entrypoint script if you have one
# ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Expose the configured FPM port (mainly informational)
EXPOSE ${FPM_LISTEN_PORT}

# Start php-fpm server
CMD ["php-fpm"]
