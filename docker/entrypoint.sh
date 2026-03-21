#!/bin/sh
set -e

# Sicherstellen, dass der data-Ordner existiert
mkdir -p /var/www/html/data

# Dem Webserver (www-data) die Rechte am data-Ordner geben
# Das korrigiert die Rechte, egal was der Host (Laptop) vorgibt
chown -R www-data:www-data /var/www/html/data
chmod -R 775 /var/www/html/data

# Den eigentlichen Befehl (Supervisor) starten
exec "$@"