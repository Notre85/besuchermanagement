#!/bin/bash

# setup.sh

# Funktion zum Anzeigen von Fehlern und Beenden des Skripts
function error_exit {
    echo "$1" 1>&2
    exit 1
}

# Überprüfen, ob das Skript als root ausgeführt wird
if [[ "$EUID" -ne 0 ]]; then
   error_exit "Bitte führen Sie das Skript als root oder mit sudo aus."
fi

# Aktualisieren der Paketlisten
echo "Aktualisieren der Paketlisten..."
apt-get update || error_exit "Fehler beim Aktualisieren der Paketlisten."

# Installation von Apache, PHP, MariaDB, Git und Composer
echo "Installieren von Apache, PHP, MariaDB, Git und Composer..."
apt-get install -y apache2 php libapache2-mod-php php-mysql mariadb-server git composer || error_exit "Fehler bei der Installation der Pakete."

# Aktivieren des Apache `mod_rewrite` Moduls
echo "Aktivieren des Apache mod_rewrite Moduls..."
a2enmod rewrite || error_exit "Fehler beim Aktivieren des mod_rewrite Moduls."

# Ermitteln der installierten PHP-Version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "Installierte PHP-Version: $PHP_VERSION"

# Aktivieren des Apache PHP-Moduls (z.B., php7.4, php8.1)
echo "Aktivieren des Apache PHP-Moduls für PHP Version $PHP_VERSION..."
a2enmod php"$PHP_VERSION" || error_exit "Fehler beim Aktivieren des PHP-Moduls."

# Apache neu starten, um die Änderungen zu übernehmen
echo "Neustarten von Apache..."
systemctl restart apache2 || error_exit "Fehler beim Neustarten von Apache."

# Deaktivieren der Apache-Standardseite
echo "Deaktivieren der Apache-Standardseite..."
a2dissite 000-default.conf || error_exit "Fehler beim Deaktivieren der Standardseite."

# Klonen des GitHub-Repositories
echo "Klonen des GitHub-Repositories..."
git clone https://github.com/Notre85/besuchermanagement.git /var/www/besuchermanagement || error_exit "Fehler beim Klonen des Repositories."

# Wechseln zum Projektverzeichnis
cd /var/www/besuchermanagement || error_exit "Fehler beim Wechseln in das Projektverzeichnis."

# Prüfen, ob composer.json existiert und Composer-Abhängigkeiten installieren
if [ -f "composer.json" ]; then
    echo "composer.json gefunden. Installieren der Composer-Abhängigkeiten..."
    composer install || error_exit "Fehler bei der Installation der Composer-Abhängigkeiten."
else
    echo "composer.json nicht gefunden. Überspringen der Composer-Installation."
fi

# Setzen der Ordnerberechtigungen
echo "Setzen der Ordnerberechtigungen..."
chown -R www-data:www-data /var/www/besuchermanagement || error_exit "Fehler beim Setzen des Eigentümers."
chmod -R 755 /var/www/besuchermanagement || error_exit "Fehler beim Setzen der Berechtigungen."

# Generieren eines zufälligen Datenbankpassworts
echo "Generieren eines zufälligen Datenbankpassworts..."
DB_PASSWORD=$(openssl rand -base64 12) || error_exit "Fehler bei der Generierung des Datenbankpassworts."
echo "Generiertes Datenbankpasswort: $DB_PASSWORD"

# MariaDB einrichten
echo "Einrichten von MariaDB..."
mysql -e "CREATE DATABASE besuchermanagement CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || error_exit "Fehler beim Erstellen der Datenbank."
mysql -e "CREATE USER 'bm_user'@'localhost' IDENTIFIED BY '$DB_PASSWORD';" || error_exit "Fehler beim Erstellen des Datenbankbenutzers."
mysql -e "GRANT ALL PRIVILEGES ON besuchermanagement.* TO 'bm_user'@'localhost';" || error_exit "Fehler beim Gewähren der Berechtigungen."
mysql -e "FLUSH PRIVILEGES;" || error_exit "Fehler beim Aktualisieren der Berechtigungen."

# Importieren des Datenbankschemas
echo "Importieren des Datenbankschemas..."
mysql -u bm_user -p"$DB_PASSWORD" besuchermanagement < database/schema.sql || error_exit "Fehler beim Importieren des Datenbankschemas."

# Admin-Benutzer anlegen
echo "Erstellen des ersten Admin-Benutzers..."
read -p 'Bitte geben Sie den Benutzernamen für den Admin-Benutzer ein: ' ADMIN_USERNAME
while [[ -z "$ADMIN_USERNAME" ]]; do
    echo "Benutzername darf nicht leer sein."
    read -p 'Bitte geben Sie den Benutzernamen für den Admin-Benutzer ein: ' ADMIN_USERNAME
done

read -s -p 'Bitte geben Sie das Passwort für den Admin-Benutzer ein: ' ADMIN_PASSWORD
echo
read -s -p 'Bitte bestätigen Sie das Passwort: ' ADMIN_PASSWORD_CONFIRM
echo

# Passwort-Überprüfung
while [ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRM" ]; do
    echo "Passwörter stimmen nicht überein. Bitte erneut eingeben."
    read -s -p 'Bitte geben Sie das Passwort für den Admin-Benutzer ein: ' ADMIN_PASSWORD
    echo
    read -s -p 'Bitte bestätigen Sie das Passwort: ' ADMIN_PASSWORD_CONFIRM
    echo
done

# Hashen des Admin-Passworts
echo "Hashen des Admin-Passworts..."
HASHED_PASSWORD=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_BCRYPT);") || error_exit "Fehler beim Hashen des Passworts."

# Admin-Benutzer in die Datenbank einfügen
echo "Einfügen des Admin-Benutzers in die Datenbank..."
mysql -u bm_user -p"$DB_PASSWORD" besuchermanagement -e "INSERT INTO users (username, password, first_name, last_name, role_id) VALUES ('$ADMIN_USERNAME', '$HASHED_PASSWORD', 'Admin', 'User', 1);" || error_exit "Fehler beim Einfügen des Admin-Benutzers."

# .env-Datei erstellen
echo "Erstellen der .env-Datei..."
cat <<EOF > config/.env
APP_DEBUG=false
DB_HOST=localhost
DB_NAME=besuchermanagement
DB_USER=bm_user
DB_PASS=$DB_PASSWORD
EOF || error_exit "Fehler beim Erstellen der .env-Datei."

# Apache-Konfiguration erstellen
echo "Erstellen der Apache-Konfigurationsdatei..."
cat <<EOF > /etc/apache2/sites-available/besuchermanagement.conf
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
EOF || error_exit "Fehler beim Erstellen der Apache-Konfigurationsdatei."

# Aktivieren der neuen Site
echo "Aktivieren der Besuchermanagement Apache-Site..."
a2ensite besuchermanagement.conf || error_exit "Fehler beim Aktivieren der Besuchermanagement Site."

# Apache neu starten, um die Änderungen zu übernehmen
echo "Neustarten von Apache..."
systemctl restart apache2 || error_exit "Fehler beim Neustarten von Apache."

# Entfernen der Test-PHP-Datei (falls vorhanden)
if [ -f "test.php" ]; then
    rm test.php
    echo "Entfernen der Test-PHP-Datei..."
fi

# Abschlussmeldung
echo "Installation abgeschlossen. Sie können das Besuchermanagement-System jetzt unter http://localhost aufrufen."
echo "Admin-Benutzername: $ADMIN_USERNAME"
echo "Admin-Passwort: $ADMIN_PASSWORD"
