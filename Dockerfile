FROM php:8.3-fpm-alpine

# System-Abhängigkeiten & User-Management
# Wir stellen sicher, dass www-data existiert (Alpine-Fix)
RUN set -x ; \
    addgroup -g 82 -S www-data ; \
    adduser -u 82 -D -S -G www-data www-data && exit 0 ; exit 1

RUN apk add --no-cache nginx supervisor

# PHP Extensions
RUN docker-php-ext-install pdo pdo_mysql

# Arbeitsverzeichnis
WORKDIR /var/www/html

# Zuerst Verzeichnisstruktur erzwingen
RUN mkdir -p /var/www/html/data \
             /var/www/html/config \
             /var/www/html/public \
             /run/nginx \
             /var/log/supervisor

# Projektdateien kopieren
COPY . .

# Jetzt die Berechtigungen setzen (rekursiv)
RUN chown -R www-data:www-data /var/www/html

# Nginx & Supervisor Config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]