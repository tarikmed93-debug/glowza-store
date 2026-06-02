FROM wordpress:php8.2-apache

# WP-CLI
RUN curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# SQLite driver PHP (remplace MySQL pour le démo)
RUN docker-php-ext-install pdo pdo_sqlite 2>/dev/null || true

# OPcache
RUN docker-php-ext-install opcache && \
    echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=4000" \
    >> /usr/local/etc/php/conf.d/opcache.ini

# SQLite integration WordPress (drop-in officiel)
RUN mkdir -p /var/www/html/wp-content/database && \
    curl -sL https://raw.githubusercontent.com/WordPress/sqlite-database-integration/main/db.php \
    -o /var/www/html/wp-content/db.php

# Copier notre child theme
COPY theme/child/ /var/www/html/wp-content/themes/glowza/

# Copier nos mu-plugins
COPY mu-plugins/ /var/www/html/wp-content/mu-plugins/

# Copier nos plugins custom
COPY plugins/pixel-manager/  /var/www/html/wp-content/plugins/pixel-manager/
COPY plugins/dsers-ready/    /var/www/html/wp-content/plugins/dsers-ready/
COPY plugins/glowza-tweaks/  /var/www/html/wp-content/plugins/glowza-tweaks/

# wp-config pour SQLite
RUN echo "<?php \
define('DB_DIR', '/var/www/html/wp-content/database/'); \
define('DB_FILE', 'glowza.sqlite'); \
define('WP_DEBUG', false); \
define('DISALLOW_FILE_EDIT', true); \
\$table_prefix = 'glz_'; \
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/' ); \
require_once ABSPATH . 'wp-settings.php';" > /var/www/html/wp-config.php

# Permissions
RUN chown -R www-data:www-data /var/www/html/wp-content && \
    chmod -R 755 /var/www/html/wp-content

# Script d'init
COPY scripts/docker-entrypoint.sh /usr/local/bin/glowza-init.sh
RUN chmod +x /usr/local/bin/glowza-init.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/glowza-init.sh"]
