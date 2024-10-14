#!/bin/bash

# setup.sh

# Farben für die Ausgabe
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funktion zum Anzeigen von Fehlern und Beenden des Skripts
function error_exit {
    echo -e "${RED}$1${NC}" 1>&2
    exit 1
}

echo -e "${GREEN}Starte die Installation des Besuchermanagement-Systems...${NC}"

# Überprüfen, ob das Skript als root ausgeführt wird
if [[ "$EUID" -ne 0 ]]; then
   error_exit "Bitte führen Sie das Skript als root oder mit sudo aus."
fi

# Aktualisieren der Paketlisten
echo -e "${GREEN}Aktualisieren der Paketlisten...${NC}"
apt-get update || error_exit "Fehler beim Aktualisieren der Paketlisten."

# Installation von Apache, PHP, MariaDB, Git und Composer
echo -e "${GREEN}Installieren von Apache, PHP, MariaDB, Git und Composer...${NC}"
apt-get install -y apache2 php libapache2-mod-php php-mysql mariadb-server git composer || error_exit "Fehler bei der Installation der Pakete."

# Aktivieren des Apache `mod_rewrite` Moduls
echo -e "${GREEN}Aktivieren des Apache mod_rewrite Moduls...${NC}"
a2enmod rewrite || error_exit "Fehler beim Aktivieren des mod_rewrite Moduls."

# Deaktivieren des mpm_event Moduls
echo -e "${GREEN}Deaktivieren des Apache mpm_event Moduls...${NC}"
a2dismod mpm_event || error_exit "Fehler beim Deaktivieren des mpm_event Moduls."

# Aktivieren des mpm_prefork Moduls
echo -e "${GREEN}Aktivieren des Apache mpm_prefork Moduls...${NC}"
a2enmod mpm_prefork || error_exit "Fehler beim Aktivieren des mpm_prefork Moduls."

# Ermitteln der installierten PHP-Version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo -e "${GREEN}Installierte PHP-Version: $PHP_VERSION${NC}"

# Aktivieren des Apache PHP-Moduls (z.B., php8.2)
echo -e "${GREEN}Aktivieren des Apache PHP-Moduls für PHP Version $PHP_VERSION...${NC}"
a2enmod php"$PHP_VERSION" || error_exit "Fehler beim Aktivieren des PHP-Moduls."

# Apache neu starten, um die Änderungen zu übernehmen
echo -e "${GREEN}Neustarten von Apache...${NC}"
systemctl restart apache2 || error_exit "Fehler beim Neustarten von Apache."

# Deaktivieren der Apache-Standardseite
echo -e "${GREEN}Deaktivieren der Apache-Standardseite...${NC}"
a2dissite 000-default.conf || error_exit "Fehler beim Deaktivieren der Standardseite."

# Klonen des GitHub-Repositories
echo -e "${GREEN}Klonen des GitHub-Repositories...${NC}"
git clone https://github.com/Notre85/besuchermanagement.git /var/www/besuchermanagement || error_exit "Fehler beim Klonen des Repositories."

# Wechseln zum Projektverzeichnis
cd /var/www/besuchermanagement || error_exit "Fehler beim Wechseln in das Projektverzeichnis."

# Prüfen, ob composer.json existiert und Composer-Abhängigkeiten installieren
if [ -f "composer.json" ]; then
    echo -e "${GREEN}composer.json gefunden. Installieren der Composer-Abhängigkeiten...${NC}"
    composer install || error_exit "Fehler bei der Installation der Composer-Abhängigkeiten."
else
    echo -e "${YELLOW}composer.json nicht gefunden. Überspringen der Composer-Installation.${NC}"
fi

# Setzen der Ordnerberechtigungen
echo -e "${GREEN}Setzen der Ordnerberechtigungen...${NC}"
chown -R www-data:www-data /var/www/besuchermanagement || error_exit "Fehler beim Setzen des Eigentümers."
chmod -R 755 /var/www/besuchermanagement || error_exit "Fehler beim Setzen der Berechtigungen."

# Sicherstellen, dass das backup/ und logs/ Verzeichnis existieren und beschreibbar sind
echo -e "${GREEN}Erstellen und Setzen der Berechtigungen für backup/ und logs/ Verzeichnisse...${NC}"
mkdir -p /var/www/besuchermanagement/backup || error_exit "Fehler beim Erstellen des backup/ Verzeichnisses."
mkdir -p /var/www/besuchermanagement/logs || error_exit "Fehler beim Erstellen des logs/ Verzeichnisses."
chown -R www-data:www-data /var/www/besuchermanagement/backup /var/www/besuchermanagement/logs || error_exit "Fehler beim Setzen der Eigentümer für backup/ und logs/ Verzeichnisse."
chmod -R 775 /var/www/besuchermanagement/backup /var/www/besuchermanagement/logs || error_exit "Fehler beim Setzen der Berechtigungen für backup/ und logs/ Verzeichnisse."

# Generieren eines zufälligen Datenbankpassworts (falls benötigt)
echo -e "${GREEN}Generieren eines zufälligen Datenbankpassworts...${NC}"
DB_PASSWORD=$(openssl rand -base64 12) || error_exit "Fehler bei der Generierung des Datenbankpassworts."
echo "Generiertes Datenbankpasswort: $DB_PASSWORD"

# MariaDB einrichten
echo -e "${GREEN}Einrichten von MariaDB...${NC}"
mysql -e "CREATE DATABASE IF NOT EXISTS besuchermanagement CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || error_exit "Fehler beim Erstellen der Datenbank."
mysql -e "CREATE USER IF NOT EXISTS 'bm_user'@'localhost' IDENTIFIED BY '$DB_PASSWORD';" || error_exit "Fehler beim Erstellen des Datenbankbenutzers."
mysql -e "GRANT ALL PRIVILEGES ON besuchermanagement.* TO 'bm_user'@'localhost';" || error_exit "Fehler beim Gewähren der Berechtigungen."
mysql -e "FLUSH PRIVILEGES;" || error_exit "Fehler beim Aktualisieren der Berechtigungen."

# Importieren des Datenbankschemas
echo -e "${GREEN}Importieren des Datenbankschemas...${NC}"
if [ -f "schema.sql" ]; then
    mysql -u bm_user -p"$DB_PASSWORD" besuchermanagement < schema.sql || error_exit "Fehler beim Importieren des Datenbankschemas."
else
    error_exit "schema.sql Datei nicht gefunden im database/ Verzeichnis."
fi

# Admin-Benutzer anlegen (Superadmin)
echo -e "${GREEN}Erstellen des initialen Superadmin-Benutzers...${NC}"
read -p 'Bitte geben Sie den Benutzernamen für den Superadmin-Benutzer ein: ' ADMIN_USERNAME
while [[ -z "$ADMIN_USERNAME" ]]; do
    echo -e "${RED}Benutzername darf nicht leer sein.${NC}"
    read -p 'Bitte geben Sie den Benutzernamen für den Superadmin-Benutzer ein: ' ADMIN_USERNAME
done

read -s -p 'Bitte geben Sie das Passwort für den Superadmin-Benutzer ein: ' ADMIN_PASSWORD
echo
read -s -p 'Bitte bestätigen Sie das Passwort: ' ADMIN_PASSWORD_CONFIRM
echo

# Passwort-Überprüfung
while [ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRM" ]; do
    echo -e "${RED}Passwörter stimmen nicht überein. Bitte erneut eingeben.${NC}"
    read -s -p 'Bitte geben Sie das Passwort für den Superadmin-Benutzer ein: ' ADMIN_PASSWORD
    echo
    read -s -p 'Bitte bestätigen Sie das Passwort: ' ADMIN_PASSWORD_CONFIRM
    echo
done

# Hashen des Admin-Passworts
echo -e "${GREEN}Hashen des Admin-Passworts...${NC}"
HASHED_PASSWORD=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_BCRYPT);") || error_exit "Fehler beim Hashen des Passworts."

# Admin-Benutzer in die Datenbank einfügen
echo -e "${GREEN}Einfügen des Superadmin-Benutzers in die Datenbank...${NC}"
mysql -u bm_user -p"$DB_PASSWORD" besuchermanagement <<EOF
INSERT INTO users (username, password, first_name, last_name, role, created_at)
VALUES ('$ADMIN_USERNAME', '$HASHED_PASSWORD', 'Super', 'Admin', 'Superadmin', NOW())
ON DUPLICATE KEY UPDATE username=username;
EOF

if [ $? -ne 0 ]; then
    error_exit "Superadmin-Benutzer konnte nicht erstellt werden. Möglicherweise existiert bereits ein Benutzer mit diesem Benutzernamen."
fi

echo -e "${GREEN}Superadmin-Benutzer erfolgreich erstellt.${NC}"

# .env-Datei erstellen
echo -e "${GREEN}Erstellen der .env-Datei...${NC}"

# .env-Datei erstellen
cat <<ENVEOF > config/.env
DB_HOST=localhost
DB_NAME=besuchermanagement
DB_USER=bm_user
DB_PASS=$DB_PASSWORD
DISPLAY_ERRORS=false
ENVEOF

# Fehlerbehandlung
if [ $? -ne 0 ]; then
    echo "Fehler beim Erstellen der .env-Datei."
    exit 1
fi

echo "Die .env-Datei wurde erfolgreich erstellt."

# Apache-Konfiguration erstellen
echo -e "${GREEN}Erstellen der Apache-Konfigurationsdatei...${NC}"

# Apache-Konfigurationsdatei erstellen
cat <<APEOF > /etc/apache2/sites-available/besuchermanagement.conf
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
APEOF

# Fehlerbehandlung
if [ $? -ne 0 ]; then
    echo "Fehler beim Erstellen der Apache-Konfigurationsdatei."
    exit 1
fi

echo "Die Apache-Konfigurationsdatei wurde erfolgreich erstellt."

# Aktivieren der neuen Site
echo -e "${GREEN}Aktivieren der Besuchermanagement Apache-Site...${NC}"
a2ensite besuchermanagement.conf || error_exit "Fehler beim Aktivieren der Besuchermanagement Site."

# Apache neu starten, um die Änderungen zu übernehmen
echo -e "${GREEN}Neustarten von Apache...${NC}"
systemctl restart apache2 || error_exit "Fehler beim Neustarten von Apache."

# Entfernen der Test-PHP-Datei (falls vorhanden)
if [ -f "test.php" ]; then
    rm test.php
    echo -e "${GREEN}Entfernen der Test-PHP-Datei...${NC}"
fi

# Blockieren des setup.sh Skripts nach Abschluss der Installation
#echo -e "${GREEN}Blockiere das Setup-Skript...${NC}"
#mv setup.sh setup.sh.bak || error_exit "Fehler beim Umbenennen des setup.sh Skripts."

# Abschlussmeldung
echo -e "${GREEN}Installation abgeschlossen. Sie können das Besuchermanagement-System jetzt unter http://localhost aufrufen.${NC}"
echo -e "${GREEN}Superadmin-Benutzername: $ADMIN_USERNAME${NC}"
echo -e "${GREEN}Superadmin-Passwort: $ADMIN_PASSWORD${NC}"
