FROM php:8.3-fpm-alpine

# 1. System-Abhängigkeiten & PHP-Build-Deps
# Wir nutzen virtuelle Pakete (.build-deps), um sie später leicht löschen zu können
RUN apk add --no-cache \
    nginx \
    supervisor \
    sed \
    libpq \
    libcap \
    sqlite-libs && \
    apk add --no-cache --virtual .build-deps \
    sqlite-dev \
    postgresql-dev && \
    docker-php-ext-install pdo pdo_sqlite pdo_pgsql && \
    # Aufräumen: Build-Abhängigkeiten entfernen
    apk del .build-deps && \
    # Nginx Logs nach stdout/stderr umleiten für Docker Logs
    ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log

# 2. Arbeitsverzeichnis setzen
WORKDIR /var/www/html

# 3. Konfigurationen kopieren (Ändern sich selten)
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# 4. Quellcode kopieren (Ändert sich oft)
COPY src/ .

# 5. Rechte setzen & Verzeichnisse vorbereiten
# Wir kombinieren RUN-Befehle, um Layer zu sparen
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/data && \
    # Capabilities setzen, damit Nginx auf Port 80 ohne volles Root laufen kann (optional)
    setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]