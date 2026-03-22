#!/bin/sh

# Sicherstellen, dass das Datenverzeichnis existiert und beschreibbar ist
mkdir -p /var/www/html/data
chown -R www-data:www-data /var/www/html/data
chmod -R 775 /var/www/html/data

# Falls die Datenbank noch nicht existiert, wird sie durch das PHP-Skript initialisiert
# Aber wir stellen sicher, dass die Datei selbst (falls vorhanden) die richtigen Rechte hat
if [ -f /var/www/html/data/mailshield.sqlite ]; then
    chown www-data:www-data /var/www/html/data/mailshield.sqlite
    chmod 664 /var/www/html/data/mailshield.sqlite
fi

exec "$@"