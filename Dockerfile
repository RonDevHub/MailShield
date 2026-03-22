FROM php:8.3-fpm-alpine

# Abhängigkeiten installieren
RUN apk add --no-cache nginx supervisor sed

# PHP Extensions für SQLite
RUN docker-php-ext-install pdo pdo_sqlite

# Arbeitsverzeichnis
WORKDIR /var/www/html

# Konfigurationen kopieren
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Projektdateien kopieren
COPY src/ .

# Rechte setzen
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]