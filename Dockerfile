FROM wordpress:php8.2-apache

# WP-CLI
RUN curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Extensions PHP utiles
RUN docker-php-ext-install opcache

# OPcache config (perf)
RUN echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=4000" \
    >> /usr/local/etc/php/conf.d/opcache.ini

# Copier notre child theme
COPY theme/child/ /var/www/html/wp-content/themes/glowza/

# Copier nos mu-plugins
COPY mu-plugins/ /var/www/html/wp-content/mu-plugins/

# Copier nos plugins custom
COPY plugins/pixel-manager/  /var/www/html/wp-content/plugins/pixel-manager/
COPY plugins/dsers-ready/    /var/www/html/wp-content/plugins/dsers-ready/
COPY plugins/glowza-tweaks/  /var/www/html/wp-content/plugins/glowza-tweaks/

# Script d'init
COPY scripts/docker-entrypoint.sh /usr/local/bin/glowza-init.sh
RUN chmod +x /usr/local/bin/glowza-init.sh

ENTRYPOINT ["/usr/local/bin/glowza-init.sh"]
