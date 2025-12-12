# =========================
# Stage 1: Builder
# =========================
FROM php:8.2-fpm-alpine AS builder

# Build arguments
ARG XDEBUG_ENABLED=false
ARG APP_ENV_BUILD=production
ENV APP_ENV=$APP_ENV_BUILD

WORKDIR /var/www/html

# Install system dependencies for PHP extensions and Node.js
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    curl \
    nodejs \
    npm \
    icu-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    libxml2-dev \
    libzip-dev \
    zlib-dev \
    openssl-dev \
    linux-headers \
    pkgconfig \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip \
    libpng \
    libjpeg-turbo \
    && rm -rf /var/cache/apk/*

# Configure and install PHP extensions (compiles .so files)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl pdo pdo_mysql mysqli mbstring zip xml sockets bcmath \
    && if [ "$XDEBUG_ENABLED" = "true" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi

# Special handling for Redis extension (PECL)
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY package.json package-lock.json ./
COPY . .

RUN npm install

RUN if [ "$APP_ENV_BUILD" = "production" ]; then \
    npm run build; \
    fi

# =========================
# Stage 2: Production (Final Image)
# =========================
FROM php:8.2-fpm-alpine AS production

ARG XDEBUG_ENABLED=false
ARG APP_ENV_BUILD=production
ENV APP_ENV=$APP_ENV_BUILD

WORKDIR /var/www/html

# Install only essential runtime system dependencies
RUN apk add --no-cache \
    bash \
    icu \
    libzip \
    zlib \
    libxml2 \
    freetype \
    libpng \
    libjpeg-turbo \
    imagemagick \
    oniguruma \
    acl \
    sqlite-libs \
    && rm -rf /var/cache/apk/*

# Copy PHP extensions' .so files from the builder stage
COPY --from=builder /usr/local/lib/php/extensions/no-debug-non-zts-20220829/ /usr/local/lib/php/extensions/no-debug-non-zts-20220829/

# Copy PHP config files from the builder stage. This should include extension .ini files.
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# --- CRITICAL FIX: Explicitly ensure ALL standard extensions are ENABLED ---
# Even after copying conf.d, some core extensions might need this for full activation in the final image's runtime
RUN docker-php-ext-enable \
    pdo_mysql \
    zip \
    gd \
    mbstring \
    opcache \
    bcmath \
    sockets \
    redis
# --- END CRITICAL FIX ---

# Copy Composer from builder stage
COPY --from=builder /usr/bin/composer /usr/bin/composer

# Copy Composer vendor directory and Node modules from the builder stage
COPY --from=builder /var/www/html/vendor ./vendor
COPY --from=builder /var/www/html/node_modules ./node_modules

# Copy built public assets (for production builds)
COPY --from=builder /var/www/html/public/build ./public/build

# Install Xdebug if XDEBUG_ENABLED is true (for development)
# This installs xdebug in the final stage if requested.
RUN if [ "$XDEBUG_ENABLED" = "true" ]; then \
    pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=VSCODE" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=false" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    fi

# Copy the rest of the application code AFTER installing dependencies
COPY . /var/www/html

# Copy custom php.ini
COPY docker/php-fpm/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Clear Laravel caches and optimize for production
RUN if [ "$APP_ENV" = "production" ]; then \
    php artisan optimize:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache; \
    fi

# Set appropriate permissions for Laravel storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]