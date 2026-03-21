FROM php:8.3-fpm-alpine

# System Abhängigkeiten
RUN apk add --no-cache nginx supervisor

# PHP Extensions (PDO für SQLite/MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Verzeichnisse erstellen
RUN mkdir -p /var/www/html /run/nginx /var/log/supervisor

# Arbeitsverzeichnis
WORKDIR /var/www/html

# Dateien kopieren
COPY . .

# Berechtigungen für SQLite und Config
RUN chown -R www-data:www-data /var/www/html/data /var/www/html/config

# Nginx Config einspielen (muss erstellt werden)
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n", "-c", "/var/www/html/docker/supervisord.conf"]