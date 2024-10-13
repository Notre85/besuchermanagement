#!/bin/bash

# Setup-Skript für das Besuchermanagement-System

# Farben für die Ausgabe
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starte die Installation des Besuchermanagement-Systems...${NC}"

# Überprüfen, ob Composer installiert ist
if ! command -v composer &> /dev/null
then
    echo -e "${RED}Composer ist nicht installiert. Bitte installieren Sie Composer und führen Sie das Skript erneut aus.${NC}"
    exit 1
fi

# Installation von Composer-Abhängigkeiten
echo -e "${GREEN}Installiere Composer-Abhängigkeiten...${NC}"
composer install

# Erstellen der Datenbank
echo -e "${GREEN}Erstelle die Datenbank...${NC}"
DB_NAME=$(grep DB_NAME .env | cut -d '=' -f2)
DB_USER=$(grep DB_USER .env | cut -d '=' -f2)
DB_PASS=$(grep DB_PASS .env | cut -d '=' -f2)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)

mysql -u$DB_USER -p$DB_PASS -h$DB_HOST -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"

# Import des Schemas
echo -e "${GREEN}Importiere das Datenbankschema...${NC}"
mysql -u$DB_USER -p$DB_PASS -h$DB_HOST $DB_NAME < schema.sql

# Erstellung des initialen Superadmin-Benutzers
echo -e "${GREEN}Erstelle den initialen Superadmin-Benutzer...${NC}"
read -p "Geben Sie den Benutzernamen für den Superadmin ein: " SUPERADMIN_USERNAME
read -sp "Geben Sie das Passwort für den Superadmin ein: " SUPERADMIN_PASSWORD
echo
read -p "Geben Sie den Vornamen des Superadmins ein: " SUPERADMIN_FIRSTNAME
read -p "Geben Sie den Nachnamen des Superadmins ein: " SUPERADMIN_LASTNAME

HASHED_PASSWORD=$(php -r "echo password_hash('$SUPERADMIN_PASSWORD', PASSWORD_BCRYPT);")

mysql -u$DB_USER -p$DB_PASS -h$DB_HOST $DB_NAME -e "INSERT INTO users (username, password, first_name, last_name, role, created_at) VALUES ('$SUPERADMIN_USERNAME', '$HASHED_PASSWORD', '$SUPERADMIN_FIRSTNAME', '$SUPERADMIN_LASTNAME', 'Superadmin', NOW());"

# Setzen der richtigen Dateiberechtigungen
echo -e "${GREEN}Setze die Dateiberechtigungen...${NC}"
chmod -R 755 /var/www/besuchermanagement/
chown -R www-data:www-data /var/www/besuchermanagement/

# Blockieren von Installationsskripten nach Abschluss der Installation
echo -e "${GREEN}Blockiere Installationsskripte...${NC}"
mv setup.sh setup.sh.bak
echo "Setup abgeschlossen. Die Installationsskripte wurden blockiert."

echo -e "${GREEN}Installation erfolgreich abgeschlossen!${NC}"
