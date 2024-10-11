#!/bin/bash

# setup.sh

# Aktualisieren der Paketlisten
sudo apt-get update

# Installation von Apache, PHP, MariaDB, Git und Composer
sudo apt-get install -y apache2 php php-mysql libapache2-mod-php mariadb-server git composer

# Apache-Module aktivieren
sudo a2enmod rewrite

# Apache neu starten, um die Änderungen zu übernehmen
sudo systemctl restart apache2

# Klonen des GitHub-Repositories
sudo git clone https://github.com/Notre85/besuchermanagement.git /var/www/besuchermanagement

# Wechseln zum Projektverzeichnis
cd /var/www/besuchermanagement

# Composer-Abhängigkeiten installieren
composer install

# Setzen der Ordnerberechtigungen
sudo chown -R www-data:www-data /var/www/besuchermanagement
sudo chmod -R 755 /var/www/besuchermanagement

# MariaDB einrichten
DB_PASSWORD=$(openssl rand -base64 12)
echo "Generiertes Datenbankpasswort: $DB_PASSWORD"

sudo mysql -e "CREATE DATABASE besuchermanagement CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'bm_user'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
sudo mysql -e "GRANT ALL PRIVILEGES ON besuchermanagement.* TO 'bm_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Importieren des Datenbankschemas
sudo mysql -u bm_user -p"$DB_PASSWORD" besuchermanagement < database/schema.sql

# Admin-Benutzer anlegen
read -p 'Bitte geben Sie den Benutzernamen für den Admin-Benutzer ein: ' ADMIN_USERNAME
read -s -p 'Bitte geben Sie das Passwort für den Admin-Benutzer ein: ' ADMIN_PASSWORD
echo
read -s -p 'Bitte bestätigen Sie das Passwort: ' ADMIN_PASSWORD_CONFIRM
echo

while [ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRM" ]; do
    echo "Passwörter stimmen nicht überein. Bitte erneut eingeben."
    read -s -p 'Bitte geben Sie das Passwort für den Admin-Benutzer ein: ' ADMIN_PASSWORD
    echo
    read -s -p 'Bitte bestätigen Sie das Passwort: ' ADMIN_PASSWORD_CONFIRM
    echo
done

# Hashen des Admin-Passworts
HASHED_PASSWORD=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_BCRYPT);")

# Admin-Benutzer in die Datenbank einfügen
sudo mysql -u bm_user -p"$DB_PASSWORD" besuchermanagement -e "INSERT INTO users (username, password, first_name, last_name, role_id) VALUES ('$ADMIN_USERNAME', '$HASHED_PASSWORD', 'Admin', 'User', 1);"

# .env-Datei erstellen
cat <<EOF > config/.env
APP_DEBUG=false
DB_HOST=localhost
DB_NAME=besuchermanagement
DB_USER=bm_user
DB_PASS=$DB_PASSWORD
EOF

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

    ErrorLog \${APACHE_LOG_DIR}/besuchermanagement_error.log
    CustomLog \${APACHE_LOG_DIR}/besuchermanagement_access.log combined
</VirtualHost>
EOF'

# Aktivieren der neuen Site
sudo a2ensite besuchermanagement.conf

# Apache neu starten
sudo systemctl restart apache2

echo "Installation abgeschlossen. Sie können das Besuchermanagement-System jetzt unter http://localhost aufrufen."
echo "Admin-Benutzername: $ADMIN_USERNAME"
