#!/bin/bash

# setup.sh

# Aktualisieren der Paketlisten
sudo apt-get update

# Installation von Apache, PHP, MariaDB/MySQL, Git und Composer
sudo apt-get install -y apache2 php php-mysql libapache2-mod-php mariadb-server git composer

# Klonen des GitHub-Projekts (ersetzen Sie den Platzhalter durch das tatsächliche Repository)
sudo git clone https://github.com/IhrBenutzername/besuchermanagement.git /var/www/besuchermanagement

# Wechseln zum Projektverzeichnis
cd /var/www/besuchermanagement

# Composer-Abhängigkeiten installieren
composer install

# Datenbank einrichten
sudo mysql -e "CREATE DATABASE besuchermanagement CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'bm_user'@'localhost' IDENTIFIED BY 'bm_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON besuchermanagement.* TO 'bm_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
sudo mysql besuchermanagement < database/schema.sql

# .env-Datei erstellen
cp config/.env.example config/.env

# Dateiberechtigungen setzen
sudo chown -R www-data:www-data /var/www/besuchermanagement
sudo chmod -R 755 /var/www/besuchermanagement

# Apache-Konfiguration erstellen
sudo bash -c 'cat > /etc/apache2/sites-available/besuchermanagement.conf <<EOF
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/besuchermanagement

    <Directory /var/www/besuchermanagement>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/besuchermanagement_error.log
    CustomLog ${APACHE_LOG_DIR}/besuchermanagement_access.log combined
</VirtualHost>
EOF'

# Aktivieren der neuen Site und Modulen
sudo a2ensite besuchermanagement.conf
sudo a2enmod rewrite

# Apache neu starten
sudo systemctl restart apache2
