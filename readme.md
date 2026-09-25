# Besuchermanagement-System

## Projektübersicht

Ein webbasiertes System zur effizienten Verwaltung von Besucher-Check-ins und -Check-outs. Administrieren von Benutzern, Erstellen von Berichten und Sicherstellen der Datensicherheit sind zentrale Funktionen.

## Technologien

- **Backend:** PHP (mit PDO für Datenbankinteraktionen)
- **Frontend:** HTML, CSS, JavaScript
- **Datenbank:** MariaDB/MySQL
- **Architektur:** Model-View-Controller (MVC)
- **Bibliotheken und Tools:**
  - Composer: Abhängigkeitsmanagement
  - Monolog: Für Logging
  - TCPDF: Für PDF-Generierung
  - phpdotenv: Für das Laden von Umgebungsvariablen aus .env

## Installation

Die produktive Umgebungsdatei liegt außerhalb des Webroots unter
`/var/www/besuchermanagement.env`. Sie enthält Datenbankzugänge und darf nicht
in das Repository übernommen werden. Für abweichende Installationen kann der
Pfad über `BESUCHERMANAGEMENT_ENV_FILE` gesetzt werden.

1. **Voraussetzungen:**
   - PHP >= 7.4
   - Composer
   - MariaDB/MySQL
   - Webserver (z.B. Apache, Nginx)

2. **Setup-Skript ausführen:**
   ```bash
   bash setup.sh
   ```

## Betrieb und Sicherung

Die ausführliche Beschreibung von Systemaufbau, Rollen, Sicherheitsmaßnahmen,
Betrieb, Wiederherstellung und offenen Produktionsnachweisen steht in der
[Sicherheits- und Betriebsdokumentation](SICHERHEITSBETRIEBS-DOKUMENTATION.md).
Die Verarbeitung personenbezogener Daten ist im
[Datenschutzkonzept](DATENSCHUTZKONZEPT.md) beschrieben.

Die Anwendung sollte im Produktivbetrieb ausschließlich über HTTPS erreichbar sein.
Die HTTPS-Umstellung ist aktuell bewusst noch offen. Der Datenbankbenutzer
sollte nur die für den Betrieb benötigten Rechte erhalten. Backups werden unter
`/var/backups/besuchermanagement` mit restriktiven Dateirechten abgelegt und müssen
regelmäßig in einer getrennten Testdatenbank wiederhergestellt werden.

Für die automatische Anonymisierung alter Besucherdaten kann der CLI-Befehl mit
`RETENTION_DAYS` ausgeführt werden:

```bash
php bin/retention.php
```

Der Aufbewahrungslauf verarbeitet zusätzlich Auditdaten; ein täglicher Cronjob ist
für den Produktivbetrieb vorgesehen.

Für das automatische Markieren abgelaufener Termine kann zusätzlich ein Cronjob
eingerichtet werden, zum Beispiel stündlich:

```cron
0 * * * * www-data /usr/bin/php /var/www/besuchermanagement/bin/expire_planned_visits.php
```

Der öffentliche Empfang ist über `kiosk.php` vorgesehen. Die Verwaltungsoberfläche
liegt auf `index.php`; nicht angemeldete Aufrufe zeigen dort die Loginseite. Der Kiosk
sollte zusätzlich auf ein eigenes Empfangsgerät oder Netzsegment begrenzt werden.

Für eine zusätzliche Geräteprüfung kann in der externen Umgebungsdatei
`KIOSK_DEVICE_TOKEN` gesetzt werden. Dann muss ein vorgeschalteter Proxy oder eine
verwaltete Kiosk-Anwendung den Wert als `X-Kiosk-Device-Token` an `kiosk.php`
übergeben. Ohne gesetzte Variable bleibt die bestehende Netzfreigabe maßgeblich.

`robots.txt` sperrt kooperative Crawler für die interne Anwendung. Der standardisierte
Sicherheitskontakt liegt unter `/.well-known/security.txt`; vor dem Produktivbetrieb
muss dort die interne Adresse eingetragen werden. Aktuell ist
`mailto:iuk.ilsmfrs@brk.de` hinterlegt; bevorzugte Sprache ist Deutsch.

Angemeldete Empfangssitzungen laufen nach acht Stunden Inaktivität ab. Die Dauer
kann in der externen Umgebungsdatei mit `SESSION_IDLE_TIMEOUT` in Sekunden
angepasst werden, zum Beispiel `SESSION_IDLE_TIMEOUT=28800`.

### Kiosk-Druckagent

Auf dem Kiosk-Endgerät werden CUPS, `curl`, `jq` und `lp` eingerichtet. Der Agent
verwendet die in der Anwendung konfigurierte Gerätekennung und das geheime Token:

```bash
export PRINT_AGENT_URL=https://besuchermanagement.intern
export PRINT_AGENT_TOKEN='<Token aus der Serverkonfiguration>'
export PRINT_AGENT_DEVICE_ID=kiosk-01
export CUPS_DESTINATION=besucher-bon
/var/www/besuchermanagement/bin/kiosk-print-agent.sh
```

Für einen Labeldrucker wird ein zweites CUPS-Ziel eingerichtet und über ein eigenes
Druckprofil verwendet. Beide Profile können dieselben Besucherausweis- und
Schlüsselvorlagen mit unterschiedlichen Papiermaßen nutzen.

Schlüssel werden nicht automatisch beim Check-in ausgegeben. Ein angemeldeter
Benutzer kann beim aktiven Besuch über „Schlüssel ausgeben“ einen konkreten
verfügbaren Schlüssel auswählen. Die Auswahl öffnet eine Lightbox; danach werden
Schlüssel-BON und Schlüssel-Label als Druckjobs eingereiht. Die Ausgabe und
Rückgabe bleiben in der Schlüsselhistorie nachvollziehbar.
