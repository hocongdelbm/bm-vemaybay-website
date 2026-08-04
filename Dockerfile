FROM php:8.2-apache

# Install required libraries
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    zip unzip git libicu-dev libxml2-dev libcurl4-openssl-dev cron \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install gd mysqli pdo pdo_mysql intl zip opcache xml curl \
    && a2enmod rewrite headers \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Consolidate all PHP ini settings into one layer
RUN cat > /usr/local/etc/php/conf.d/suitecrm.ini <<'EOF'
; Memory & Upload
memory_limit = 512M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_time = 300

; Error reporting (switch display_errors=Off in production)
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE
display_errors = On
log_errors = On

; Session
session.gc_maxlifetime = 7200

; Upload tmp — same filesystem as SuiteCRM cache to avoid cross-device rename errors
upload_tmp_dir = /var/www/html/cache/tmp
EOF

# OPcache tuning (SuiteCRM benefits significantly from this)
RUN cat > /usr/local/etc/php/conf.d/opcache.ini <<'EOF'
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 16000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
EOF

# Apache: allow .htaccess overrides globally and in the vhost
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Dedicated Apache vhost for SuiteCRM
RUN cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:80>
    DocumentRoot /var/www/html
    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Pre-create dirs that SuiteCRM needs to write to.
# These exist in the image so the entrypoint chown is fast (only new bind-mount content).
RUN mkdir -p \
    /var/www/html/cache/tmp \
    /var/www/html/cache/modules \
    /var/www/html/custom \
    /var/www/html/modules \
    /var/www/html/upload \
    /var/www/html/upload/import

WORKDIR /var/www/html

# Copy and wire up entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]