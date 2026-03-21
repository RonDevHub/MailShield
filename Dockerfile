FROM php:8.3-fpm-alpine

# System-Abhängigkeiten
# Wir installieren nginx und supervisor. 
# Falls www-data nicht existiert, legen wir ihn an, sonst ignorieren wir den Fehler.
RUN apk add --no-cache nginx supervisor \
    && set -x \
    && addgroup -g 82 -S www-data || true \
    && adduser -u 82 -D -S -G www-data www-data || true

# PHP Extensions für SQLite und MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Arbeitsverzeichnis festlegen
WORKDIR /var/www/html

# Verzeichnisse im Container vorbereiten
RUN mkdir -p /var/www/html/data \
             /var/www/html/config \
             /var/www/html/public \
             /run/nginx \
             /var/log/supervisor

# Projektdateien kopieren
COPY . .

# Berechtigungen setzen (ganz wichtig für SQLite!)
# Wir geben www-data die Gewalt über das Verzeichnis
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Konfigurationsdateien an die richtigen Stellen schieben
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

# Start über Supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]