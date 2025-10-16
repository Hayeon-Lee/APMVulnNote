FROM php:8.2-apache
RUN a2enmod rewrite \
 && docker-php-ext-install pdo_mysql
WORKDIR /var/www/html
COPY docker/apache.vhost.conf /etc/apache2/sites-available/000-default.conf
# 업로드/로그 경로 준비 (웹루트 밖)
RUN mkdir -p /storage/uploads /var/log/app && chown -R www-data:www-data /storage /var/log/app