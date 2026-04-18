FROM wordpress:php8.3-apache

# Railway 上直接跑官方 wordpress 镜像时，Apache 可能出现多 MPM 同时加载。
# 这里不依赖镜像默认 CMD，而是显式复用已在旧 Primary 上验证过的启动链路。
ENTRYPOINT ["/bin/bash", "-lc", "echo 'ServerName 0.0.0.0' >> /etc/apache2/apache2.conf && echo 'DirectoryIndex index.php index.html' >> /etc/apache2/apache2.conf && echo 'upload_max_filesize = 50M' >> /usr/local/etc/php/php.ini && echo 'post_max_size = 50M' >> /usr/local/etc/php/php.ini && docker-entrypoint.sh a2dismod mpm_event && a2dismod mpm_worker && a2enmod mpm_prefork && exec apache2-foreground"]
