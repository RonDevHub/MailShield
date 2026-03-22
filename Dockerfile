FROM php:8.3-fpm-alpine

# System-Abhängigkeiten und SQLite-Header installieren
RUN apk add --no-cache \
    nginx \
    supervisor \
    sed \
    sqlite-dev \
    libcap

# PHP Extensions installieren
# pdo_sqlite benötigt sqlite-dev während der Installation
RUN docker-php-ext-install pdo pdo_sqlite

# Arbeitsverzeichnis
WORKDIR /var/www/html

# Konfigurationen kopieren
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

COPY src/ .

# Rechte setzen & Verzeichnisse vorbereiten
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/data

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
# Startet Supervisor, der dann Nginx und PHP-FPM managt
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]