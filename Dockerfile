FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Verzeichnisse vorbereiten
RUN mkdir -p /var/www/html/data /var/www/html/config /run/nginx

COPY . .

# Das Script ausführbar machen
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80

# Das Script als Entrypoint setzen
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Supervisor bleibt der Hauptprozess
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]