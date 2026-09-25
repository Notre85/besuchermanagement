# Sicherheits- und Betriebsdokumentation

Stand: **25.09.2026**
System: **Besuchermanagement**
Betriebsmodell: **interne Anwendung auf einer eigenen VM**

## Zweck und Geltungsbereich

Das Besuchermanagement verwaltet Besucher, geplante Besuche, Check-ins,
Check-outs, Schlüssel, Besucherausweise, Druckaufträge und Auditdaten. Die
Verwaltungsoberfläche ist für angemeldete Beschäftigte bestimmt. Der Kiosk ist
eine zusätzliche, bewusst eingeschränkte Oberfläche für den Empfang. Besucher
melden sich dort selbst mit ihrer Besucher-ID an; die Schlüsselausgabe erfolgt
ausschließlich durch einen angemeldeten Beschäftigten.

Diese Dokumentation beschreibt die technische Umsetzung, die Betriebsabläufe,
die Sicherheitsmaßnahmen, die Nachweise und die noch ausstehenden
Produktionsentscheidungen. Sie ergänzt die
[Sicherheits- und Weiterentwicklungs-Checkliste](SICHERHEITS-UND-WEITERENTWICKLUNGS-CHECKLISTE.md)
und das [BSI-APP-Audit](BSI-APP-AUDIT.md).

## Statusmodell

| Status | Bedeutung |
|---|---|
| Ja | Die Funktion ist im Code umgesetzt oder als verbindlicher Betriebsprozess dokumentiert und durch einen Nachweis belegt. |
| Teilweise | Eine technische oder organisatorische Voraussetzung ist vorhanden, ein Produktionsnachweis oder eine Aktivierung fehlt noch. |
| Nein | Die Maßnahme ist noch nicht umgesetzt. |
| Nicht anwendbar | Die Maßnahme trifft auf den aktuellen internen Betrieb nicht zu. |

Ein dokumentierter Prozess ersetzt keinen technischen Nachweis. Insbesondere
HTTPS, Zertifikate, Firewallregeln, reale Dateirechte und die Konfiguration der
Kioskgeräte müssen auf der Produktions-VM geprüft werden.

## Systemaufbau

Die Anwendung besteht aus folgenden Komponenten:

| Komponente | Aufgabe | Schutzmaßnahme |
|---|---|---|
| Apache/PHP | Webserver und PHP-Ausführung | Directory-Listing aus, sensible Dateien gesperrt, Security-Header, reduzierte Serverinformationen im Setup vorgesehen |
| PHP-Anwendung | Verwaltungsoberfläche und Kiosk | Rollenprüfung, CSRF-Schutz, Eingabevalidierung, POST-only-Schreibaktionen |
| MariaDB | Besucher-, Besuchs-, Schlüssel-, Druck- und Auditdaten | eingeschränkter Datenbankbenutzer, PDO-Prepared-Statements |
| Kiosk | Selbstständiger Besucher-Check-in und Liste aktiver Besucher | Netz-Whitelist, Rate-Limits, separate Oberfläche, optionaler Gerätetoken |
| CUPS/Druckagent | Ausgabe von Ausweisen, Schlüssel-Bons und Labels | Gerätekennung, Token, Status- und Fehlerprotokollierung |
| Jasper/TCPDF/HTML-Renderer | Erstellung konfigurierbarer Druckausgaben | Vorlagen-Whitelist, Escaping, getrennte Renderer |
| Auditlog | Nachweis sicherheitsrelevanter und fachlicher Aktionen | Benutzer, Aktion, Ziel, Ergebnis, IP, Gerät und Metadaten |

Der Datenbankzugriff erfolgt ausschließlich serverseitig. Geheimnisse liegen in
`/var/www/besuchermanagement.env` außerhalb des Webroots. Backups werden unter
`/var/backups/besuchermanagement` gespeichert.

## Rollen und Verantwortlichkeiten

| Rolle | Erlaubte Aufgaben |
|---|---|
| Empfang/Kiosk | Besucher ein- und auschecken; keine Benutzer-, System- oder Vorlagenverwaltung |
| Berichtersteller | Berichte und Exporte lesen |
| Manager | Besucher, Besuche und geplante Besuche verwalten |
| Admin | Zusätzlich Benutzer, Stammdaten, Drucker, Vorlagen und Backups verwalten |
| Superadmin | Technische Gesamtadministration und Notfallverwaltung |

Der fachlich Verantwortliche entscheidet über Aufbewahrungsfristen, die
IT-Administration über VM, Apache, Datenbank, Backups und Wiederherstellung.
Die Empfangsleitung nimmt Kiosk- und Druckfunktionen fachlich ab. Namen und
Vertretungen werden vor dem Produktivbetrieb in der internen Betriebsakte
ergänzt.

## Sicherheitsmaßnahmen

### Zugriff und Sitzungen

Verwaltungsfunktionen verlangen eine Anmeldung und prüfen die Rolle unmittelbar
vor der Aktion. Passwörter werden mit `password_hash()` gespeichert und mit
`password_verify()` geprüft. Nach dem Login wird die Session-ID erneuert.
Sessions laufen standardmäßig nach acht Stunden Inaktivität ab; der Wert kann
über `SESSION_IDLE_TIMEOUT` gesetzt werden.

Session-Cookies sind `HttpOnly` und `SameSite=Lax`. Das `Secure`-Attribut wird
bei HTTPS automatisch gesetzt und muss nach der noch ausstehenden HTTPS-
Aktivierung in der Produktionskonfiguration erzwungen werden.

### Schreibaktionen und Eingaben

Schreibaktionen sind POST-only und benötigen ein gültiges CSRF-Token. Rollen,
IDs, Datumswerte, Textlängen und Statuswerte werden serverseitig validiert.
Ausgaben werden kontextbezogen escaped. Fehlerantworten zeigen keine internen
Dateipfade, SQL-Fehler oder Geheimnisse.

### Kiosk und Automatisierungsschutz

Der Kiosk ist über `KIOSK_ALLOWED_NETWORKS` auf freigegebene Netzbereiche
beschränkt. POST-Aktionen werden zusätzlich pro IP-Adresse, Session und
Gerätekennung begrenzt. Ein Gerätetoken kann über `KIOSK_DEVICE_TOKEN` aktiviert
und als `X-Kiosk-Device-Token` durch einen vertrauenswürdigen Proxy oder eine
verwaltete Kiosk-Anwendung übertragen werden. Die Aktivierung ist eine
bewusste Produktionsentscheidung, weil der aktuelle Browser-Kiosk diesen
Header nicht selbst erzeugt.

### Webserver und Dateien

`Options -Indexes` ist aktiv. Konfiguration, Quellverzeichnisse, Logs, Backups,
SQL-Dateien, Setup-Dateien, `.user.ini` und versteckte Dateien werden nicht
ausgeliefert. Apache wird im Setup mit `headers` eingerichtet; `ServerTokens
Prod`, `ServerSignature Off` und `TraceEnable Off` sind vorgesehen.

Die Anwendung setzt `Cache-Control: no-store`, `Pragma: no-cache`, CSP,
`X-Content-Type-Options`, `X-Frame-Options` und `Referrer-Policy`. Bootstrap
wird lokal aus dem Projekt ausgeliefert.

### Daten, Backups und Löschung

Backups liegen außerhalb des Webroots, werden mit restriktiven Rechten angelegt
und nach `BACKUP_RETENTION_DAYS` rotiert. Der CLI-Aufbewahrungslauf anonymisiert
alte Besucherdaten und löscht alte Auditdaten nach `RETENTION_DAYS`. Ein
Restore-Test ist vor der Produktivfreigabe und danach regelmäßig zu
dokumentieren.

### Schlüssel und Druck

Schlüssel werden nicht automatisch ausgegeben. Ein angemeldeter Beschäftigter
wählt den konkreten verfügbaren Schlüssel in der Lightbox aus. Ausgabe und
Rückgabe werden mit Besucher, Schlüssel, Zeitpunkt und verantwortlichem Benutzer
historisiert. Druckvorlagen sind versioniert und können als HTML, TCPDF/PDF oder
Jasper gerendert werden. Druckjobs besitzen Status, Fehlertext und
Wiederholungsfunktion.

## Protokollierung und Nachweisführung

Das Auditlog erfasst unter anderem Login, Check-in, Check-out,
Schlüsselausgabe, Schlüsselrückgabe, Vorlagenänderung, Druckaktionen,
Benutzeränderungen, Exporte und Backups. Jeder Eintrag enthält, soweit
vorhanden, Benutzer, Aktion, Zielobjekt, Zeitpunkt, Ergebnis, IP-Adresse,
Gerätekennung und technische Metadaten. Passwörter, Tokens und unnötige
personenbezogene Rohdaten werden nicht protokolliert.

Apache-Access- und Error-Logs werden getrennt von der Anwendung geführt. Die
Aufbewahrung und Auswertung der Logs erfolgt nach der internen Datenschutz- und
Betriebsregelung.

## Sicherheitskontakt (`security.txt`)

Der standardisierte Pfad `/.well-known/security.txt` ermöglicht es internen
Mitarbeitern oder Sicherheitsforschern, eine Schwachstelle schnell an die
zuständige Stelle zu melden. Die Datei enthält keine Zugangsdaten und keine
Anwendungsdaten.

Mindestens erforderlich ist:

```text
Contact: mailto:sicherheit@example.intern
```

Zusätzlich empfohlen sind `Expires` mit einem Datum innerhalb des nächsten
Jahres und `Preferred-Languages: de`. Optional können eine Sicherheitsrichtlinie
(`Policy`), Danksagungen (`Acknowledgments`) und eine kanonische URL
(`Canonical`) ergänzt werden. Der aktuelle Kontakt im Projekt ist ein
lautet `mailto:iuk.ilsmfrs@brk.de`.

## Betrieb und Änderung

Vor einer Änderung wird ein Datenbank-Backup erstellt. Änderungen werden über
die Checkliste beschrieben, mit `php -l`, `bash -n setup.sh`,
`composer validate`, `composer audit`, `apachectl -t` und
`bash tests/smoke.sh` geprüft. Ein Release darf erst nach erfolgreichem
Smoke-Test und fachlicher Prüfung von Login, Kiosk, Schlüssel- und Druckablauf
auf die Produktions-VM übernommen werden.

Für ein Rollback werden das vorherige Anwendungspaket und das zugehörige
Datenbankbackup aufbewahrt. Die externe Umgebungsdatei wird separat gesichert
und niemals in das Repository übernommen.

## Wiederanlauf und Notfallbetrieb

Bei Ausfall der Anwendung nimmt der Empfang Besucher zunächst nach der
internen Notfallliste auf. Schlüssel werden bis zur Wiederherstellung manuell
ausgegeben und mit Besucher, Inventarnummer, Ausgabezeit und verantwortlicher
Person erfasst. Nach dem Wiederanlauf werden die manuellen Vorgänge nachgetragen
und als nachträgliche Erfassung im Auditlog dokumentiert.

Die Wiederherstellung umfasst VM bzw. Webserver, externe Umgebungsdatei,
Datenbankbackup, Composer-Abhängigkeiten, Druckagent und CUPS-Ziele. Nach der
Wiederherstellung werden Login, Kiosk, Check-out, Schlüsselhistorie, Auditlog
und Testdruck geprüft.

## Abweichungen und offene Produktionsnachweise

Der Verzicht auf internes HTTPS ist in der
[Risikoanalyse internes HTTPS](RISIKOANALYSE-INTERNES-HTTPS.md) bewertet. Die
Entscheidung gilt nur für das beschriebene, kontrollierte interne Netz.

| Thema | Status | Erforderlicher Nachweis |
|---|---|---|
| HTTPS, Redirect und HSTS | Risiko akzeptiert | Neubewertung bei Netzwerk-, Endgeräte- oder Schutzbedarfsänderung |
| Kiosk-Gerätetoken | Teilweise | Token in der externen Umgebung aktivieren und Proxy-/Kiosk-Übertragung testen |
| Sicherheitskontakt | Ja | `mailto:iuk.ilsmfrs@brk.de`, bevorzugte Sprache Deutsch |
| Produktionsrechte und Apache-Laufzeit | Ja | Apache-Konfiguration und Dateirechte am 25.09.2026 geprüft und korrigiert |
| Penetrationstest | Geplant | Durchführung im nächsten Sicherheitszyklus |
| Fachliche Abnahme | Ja | Durch den Betreiber als erledigt bestätigt; die dokumentierten Prüfpunkte gelten als abgenommen |

### Ergebnis der VM-Prüfung vom 25.09.2026

Die Prüfung auf der aktuellen VM ergab:

- Apache lauscht auf Port 80; ein HTTPS-Listener ist nicht eingerichtet. Das
  entspricht der bewusst getroffenen Risikoentscheidung für das interne Netz.
- `display_errors` und `expose_php` sind über `.user.ini` deaktiviert.
- Die Webroot-Sperren funktionieren für Konfiguration, SQL-Dateien,
  Composer-Dateien und Setup-Dateien.
- Backups liegen außerhalb des Webroots und sind mit Modus `0600` geschützt.
- Der MariaDB-Dienst ist lokal erreichbar; ein separater Anwendungsbenutzer
  war in der Kurzprüfung nicht als privilegierter Benutzer auffindbar.
- Die laufende Apache-Konfiguration verwendet jetzt `ServerTokens Prod`,
  `ServerSignature Off` und `Options -Indexes`; die Konfiguration wurde geprüft
  und Apache anschließend neu geladen.
- Der Quellcode gehört jetzt `root:root` und ist für `www-data` nicht
  beschreibbar. Nur das Laufzeitverzeichnis `logs` gehört `www-data` und ist
  dort beschreibbar.
- Die Anwendung verwendet den eingeschränkten Datenbankbenutzer
  `bm_runtime@localhost`; die geprüften Rechte sind auf `SELECT`, `INSERT`,
  `UPDATE` und `DELETE` für die Datenbank `besuchermanagement` begrenzt.
- Der lokale Bootstrap-Pfad wurde wegen der Sperrregel für Verzeichnisse mit
  dem Namen `vendor` auf `assets/bootstrap` korrigiert.

Diese Punkte dürfen im BSI-Audit nicht als technisch vollständig umgesetzt
bezeichnet werden, solange der jeweilige Nachweis fehlt.

## Fachliche Abnahme und organisatorische Freigabe

Die fachliche Abnahme bestätigt, dass die Anwendung im Arbeitsablauf korrekt
funktioniert. Die Empfangsleitung prüft mindestens Login, Besucher-Check-in,
Check-out, aktive Besucherliste, Schlüssel-Lightbox, Schlüsselrückgabe,
Besucherausweis, Schlüssel-BON, Kiosk im Querformat und einen Fehlerfall beim
Druck. Das Ergebnis wird mit Datum, Testperson, Testdaten und offenen Mängeln
als Abnahmeprotokoll festgehalten.

Die organisatorische Freigabe ist die Entscheidung der verantwortlichen Stelle,
dass die Anwendung mit dem dokumentierten Restrisiko betrieben werden darf.
Sie bestätigt insbesondere Zweck und Geltungsbereich, Rollen und Vertretungen,
Aufbewahrungsfristen, Notfallbetrieb, Backup/Restore, Zuständigkeit für
Patches sowie die Risikoakzeptanz für den Betrieb ohne internes HTTPS. Sie ist
keine zusätzliche technische Funktion und kann durch ein unterschriebenes oder
versioniert abgelegtes Freigabedokument erfolgen.

## Zugehörige Nachweise

- [README](readme.md)
- [Sicherheits- und Weiterentwicklungs-Checkliste](SICHERHEITS-UND-WEITERENTWICKLUNGS-CHECKLISTE.md)
- [BSI-APP-Audit](BSI-APP-AUDIT.md)
- [Ausformulierte BSI-Beschreibungen](BSI-APP-AUSFORMULIERTE-BESCHREIBUNGEN.md)
- [Datenschutzkonzept](DATENSCHUTZKONZEPT.md)
- [HTTP-Sicherheitstests](tests/http_security.sh)
- [Apache-/Installationssetup](setup.sh)
