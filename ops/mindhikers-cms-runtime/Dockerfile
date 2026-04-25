FROM composer:2 AS composer-bin

FROM wordpress:php8.3-apache

# Build context must be the repository root so wordpress/** is available.
COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer

COPY wordpress/mu-plugins/ /var/www/html/wp-content/mu-plugins/
COPY wordpress/themes/ /var/www/html/wp-content/themes/
COPY wordpress/plugins-bundled/carbon-fields/ /var/www/html/wp-content/plugins/carbon-fields/
COPY wordpress/plugins-bundled/polylang/ /var/www/html/wp-content/plugins/polylang/

RUN if [ -f /var/www/html/wp-content/plugins/carbon-fields/composer.json ]; then \
      cd /var/www/html/wp-content/plugins/carbon-fields && \
      composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; \
    fi && \
    chown -R www-data:www-data /var/www/html/wp-content/mu-plugins \
                               /var/www/html/wp-content/themes \
                               /var/www/html/wp-content/plugins/carbon-fields \
                               /var/www/html/wp-content/plugins/polylang && \
    mkdir -p /opt/wp-bundle/plugins && \
    cp -r /var/www/html/wp-content/mu-plugins /opt/wp-bundle/ && \
    cp -r /var/www/html/wp-content/themes /opt/wp-bundle/ && \
    cp -r /var/www/html/wp-content/plugins/carbon-fields /opt/wp-bundle/plugins/ && \
    cp -r /var/www/html/wp-content/plugins/polylang /opt/wp-bundle/plugins/

COPY ops/wordpress/m1-seed.php /opt/wp-bundle/seed/m1-seed.php

COPY ops/mindhikers-cms-runtime/sync-bundle.sh /usr/local/bin/mh-sync-bundle.sh
RUN chmod +x /usr/local/bin/mh-sync-bundle.sh

# Railway 上直接跑官方 wordpress 镜像时，Apache 可能出现多 MPM 同时加载。
# 这里不依赖镜像默认 CMD，而是显式复用已在旧 Primary 上验证过的启动链路。
ENTRYPOINT ["/bin/bash", "-lc", "/usr/local/bin/mh-sync-bundle.sh && echo 'ServerName 0.0.0.0' >> /etc/apache2/apache2.conf && echo 'DirectoryIndex index.php index.html' >> /etc/apache2/apache2.conf && echo 'upload_max_filesize = 50M' >> /usr/local/etc/php/php.ini && echo 'post_max_size = 50M' >> /usr/local/etc/php/php.ini && docker-entrypoint.sh a2dismod mpm_event && a2dismod mpm_worker && a2enmod mpm_prefork && exec apache2-foreground"]
