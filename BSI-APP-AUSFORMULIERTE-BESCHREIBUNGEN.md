# Ausformulierte BSI-Beschreibungen

Stand: **25.09.2026**

Dieses Dokument ergänzt das [BSI-APP-Audit](BSI-APP-AUDIT.md) um eine ausformulierte Feststellung zu jeder geprüften Anforderung. Das Audit bleibt die kompakte Statusübersicht; hier werden Umsetzung, Nachweis und verbleibender Handlungsbedarf in vollständigen Sätzen beschrieben.

Die Einstufung folgt den BSI-Anforderungsstufen: **Basis**, **Standard** und **Erhöht**.

## APP.3.1 Webanwendungen und Webservices

### Basis

#### APP.3.1.A1 Authentisierung

**Umsetzungsstatus:** Teilweise erfüllt.

Verwaltungsfunktionen prüfen Login und Rolle über `controllers/BaseController.php`; Login nutzt `password_verify()` und Login-Rate-Limit. Der Kiosk erlaubt bewusst anonyme Check-ins aus freigegebenen Netzen. Auswahl und Dokumentation der angemessenen Authentisierungsmethode fehlen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.1.A4 Kontrolliertes Einbinden von Dateien und Inhalten

**Umsetzungsstatus:** Nicht anwendbar.

Es gibt keine Benutzer-Datei-Upload-Funktion. Vorlagen werden als Text über eine Admin-POST-Funktion gespeichert; zulässige Variablen und gefährliche HTML-Elemente werden serverseitig begrenzt. Für den aktuell dokumentierten Einsatzbereich ist diese Anforderung nicht anwendbar. Eine erneute Bewertung ist erforderlich, wenn sich der Betriebsumfang oder die technische Architektur ändert.

#### APP.3.1.A7 Schutz vor unerlaubter automatisierter Nutzung

**Umsetzungsstatus:** Teilweise erfüllt.

Login- und Kiosk-Rate-Limits nach IP, Sitzung und Gerätekennung, Netzwerk-Whitelist, CSRF und Auditlog sind vorhanden. Ein Geräte-Token ist optional vorbereitet, aber noch nicht produktiv aktiviert; die Besucher-ID allein bleibt erratbar. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.1.A14 Schutz vertraulicher Daten

**Umsetzungsstatus:** Teilweise erfüllt.

Passwörter werden mit `password_hash()` gespeichert; `.env` liegt außerhalb des Webroots, Quellverzeichnisse werden per `.htaccess` gesperrt und die Produktionsrechte wurden auf der VM korrigiert. Der bewusste Verzicht auf HTTPS bleibt als Risiko akzeptiert. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

### Standard

#### APP.3.1.A8 Systemarchitektur [Beschaffungsstelle]

**Umsetzungsstatus:** Teilweise erfüllt.

MVC-Struktur, getrennte Controller/Services, externe Konfiguration, Datenbank- und Druckagent-Schnittstellen sind erkennbar. Ein formales Architektur- und Schutzbedarfsdokument mit Verantwortlichen fehlt. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.1.A9 Beschaffung von Webanwendungen und Webservices

**Umsetzungsstatus:** Teilweise erfüllt.

Für die Eigenentwicklung bestehen Checkliste, `composer.lock`, Dependency-Audit und Sicherheitsfunktionen. Ein formaler Anforderungskatalog einschließlich Wartungs- und Sicherheitszusagen für externe Komponenten ist nicht nachgewiesen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.1.A11 Sichere Anbindung von Hintergrundsystemen

**Umsetzungsstatus:** Teilweise erfüllt.

MariaDB wird über PDO angesprochen; Jasper und der Druckagent verwenden definierte Schnittstellen und Token. TLS für alle Netzgrenzen sowie eine vollständige Freigabe- und Netzmatrix sind nicht nachgewiesen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.1.A12 Sichere Konfiguration

**Umsetzungsstatus:** Teilweise erfüllt.

POST-only-Schreibaktionen, generische Fehler, externe Umgebungsdatei, Limits und Router-Whitelist sind vorhanden. Eine vollständige Abschaltung unnötiger HTTP-Methoden und die produktive Webserver-Konfiguration fehlen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.1.A21 Sichere HTTP-Konfiguration bei Webanwendungen

**Umsetzungsstatus:** Teilweise erfüllt.

CSP ohne externe CDN-Quellen, Cache-Control no-store, X-Content-Type-Options, X-Frame-Options, Referrer-Policy und Cookie-Attribute sind vorhanden. HSTS und eine konsequente HTTPS-Erzwingung fehlen bewusst weiterhin. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.1.A22 Penetrationstest und Revision

**Umsetzungsstatus:** Offen.

Smoke-Tests, Syntaxprüfung und `composer audit` sind vorhanden. Ein regelmäßiger Penetrationstest, Revisionsplan, Ergebnisnachweis und ISB-Prozess sind nicht dokumentiert. Eine ausreichende Umsetzung ist derzeit nicht nachgewiesen. Die Anforderung bleibt als Maßnahme offen.

### Erhöht

#### APP.3.1.A20 Einsatz von Web Application Firewalls

**Umsetzungsstatus:** Offen / risikobasiert.

Keine WAF ist dokumentiert. Für das interne Netz ist eine WAF eine Maßnahme bei erhöhtem Schutzbedarf; eine Risikoentscheidung dazu fehlt. Eine ausreichende Umsetzung ist derzeit nicht nachgewiesen. Die Anforderung bleibt als Maßnahme offen.

## APP.3.2 Webserver

### Basis

#### APP.3.2.A1 Sichere Konfiguration eines Webservers

**Umsetzungsstatus:** Teilweise erfüllt.

`headers`, deaktivierte Directory-Listings, `ServerTokens Prod`, `ServerSignature Off` und `TraceEnable Off` sind auf der VM aktiv. Quellcodebesitz und Schreibrechte sind korrigiert; die Prüfung nicht benötigter Module und der Prozessisolation bleibt als Betriebsnachweis offen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.2.A2 Schutz der Webserver-Dateien

**Umsetzungsstatus:** Erfüllt.

`.htaccess` sperrt Konfigurations-, Quell-, Log- und Vendor-Verzeichnisse; `.env` liegt außerhalb des Webroots; die VM verwendet `Options -Indexes`; der Quellcode gehört `root:root`, nur Logs sind für `www-data` beschreibbar. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

#### APP.3.2.A3 Absicherung von Datei-Uploads und -Downloads

**Umsetzungsstatus:** Teilweise erfüllt.

Es gibt keine Benutzer-Uploads. Berichte, PDFs und Backups sind jedoch Download-/Ausgabefunktionen und müssen im laufenden Apache einschließlich Größen-, Rechte- und Malware-Konzept geprüft werden. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.2.A4 Protokollierung von Ereignissen

**Umsetzungsstatus:** Teilweise erfüllt.

Apache-Access-/Error-Logs sind im Setup vorgesehen; die Anwendung schreibt Monolog- und Auditlog-Einträge. Regelmäßige Auswertung, zentrale Aufbewahrung und Alarmierung sind nicht belegt. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.2.A5 Authentisierung

**Umsetzungsstatus:** Erfüllt.

Anwendungspasswörter werden mit `password_hash()` gespeichert und über `password_verify()` geprüft. Die Passwortdatei ist die Datenbank und wird über den eingeschränkten Laufzeitbenutzer angesprochen. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

#### APP.3.2.A7 Rechtliche Rahmenbedingungen für Webangebote

**Umsetzungsstatus:** Teilweise erfüllt.

Ein Datenschutzkonzept mit Datenkategorien, Rollen, Aufbewahrung, Betroffenenrechten, Vorfällen und Verantwortlichkeitsfeldern liegt vor. Die rechtliche Prüfung, Fristfestlegung und organisatorische Freigabe stehen noch aus. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.2.A11 Verschlüsselung über TLS

**Umsetzungsstatus:** Teilweise erfüllt / Risikoakzeptanz.

HTTPS ist bewusst nicht aktiviert. Die Abweichung, Schutzbedarf, Randbedingungen, kompensierende Maßnahmen und Auslöser für eine Neubewertung sind in `RISIKOANALYSE-INTERNES-HTTPS.md` dokumentiert. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

### Standard

#### APP.3.2.A8 Planung des Einsatzes eines Webservers

**Umsetzungsstatus:** Teilweise erfüllt.

README beschreibt Webserver, Kiosk, Druckagent und Betriebswege. Zielgruppen, Webserver-Verantwortliche, vollständige Inhalte-/Schnittstellenübersicht und Freigabeprozess fehlen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.2.A9 Sicherheitsrichtlinie für den Webserver

**Umsetzungsstatus:** Erfüllt.

Härtung, Dateischutz, Protokollierung, Änderungsverfahren, Wiederanlauf und offene Produktionsnachweise sind in `SICHERHEITSBETRIEBS-DOKUMENTATION.md` beschrieben. Die fachliche Freigabe der Richtlinie ist organisatorisch noch einzuholen. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

#### APP.3.2.A10 Auswahl eines geeigneten Webhosters

**Umsetzungsstatus:** Nicht anwendbar.

Die Anwendung ist für eine selbst betriebene interne VM vorgesehen; ein externer Webhoster ist nicht nachgewiesen. Bei späterem Hosting ist die Anforderung neu zu bewerten. Für den aktuell dokumentierten Einsatzbereich ist diese Anforderung nicht anwendbar. Eine erneute Bewertung ist erforderlich, wenn sich der Betriebsumfang oder die technische Architektur ändert.

#### APP.3.2.A12 Geeigneter Umgang mit Fehlern und Fehlermeldungen

**Umsetzungsstatus:** Teilweise erfüllt.

`display_errors` ist deaktiviert, PHP-Version wird nicht exponiert, die Anwendung verwendet generische Fehlertexte und das Setup setzt die Server-Signatur ab. Apache-Fehlerseiten und eindeutige Fehlerkorrelation sind nicht vollständig konfiguriert. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.2.A13 Zugriffskontrolle für Webcrawler

**Umsetzungsstatus:** Teilweise erfüllt.

`robots.txt` sperrt kooperative Crawler; die Anwendung ist intern und der Kiosk hat eine Netz-Whitelist. Gegen nicht kooperative Crawler besteht kein eigenständiger Schutz. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.2.A14 Integritätsprüfungen und Schutz vor Schadsoftware

**Umsetzungsstatus:** Offen.

Composer-Abhängigkeiten werden auditiert. Regelmäßige Integritätsprüfungen des Webroots, Malware-Prüfungen, Signatur-/Hashkontrollen und Alarmierung sind nicht dokumentiert. Eine ausreichende Umsetzung ist derzeit nicht nachgewiesen. Die Anforderung bleibt als Maßnahme offen.

#### APP.3.2.A16 Penetrationstest und Revision

**Umsetzungsstatus:** Teilweise erfüllt.

Der Penetrationstest ist für den nächsten Sicherheitszyklus vorgesehen. Bis dahin bestehen automatisierte Syntax-, Dependency- und HTTP-Sicherheitstests. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.3.2.A20 Benennung von Anzusprechenden

**Umsetzungsstatus:** Erfüllt.

Rollen sind dokumentiert. Der Sicherheitskontakt `mailto:iuk.ilsmfrs@brk.de` ist in `security.txt` und `/.well-known/security.txt` hinterlegt. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

### Erhöht

#### APP.3.2.A15 Redundanz

**Umsetzungsstatus:** Nicht bewertet / risikobasiert.

Für eine einzelne interne VM ist keine Redundanz implementiert. Die Notwendigkeit muss aus Verfügbarkeitsanforderungen und Schutzbedarf entschieden werden. Die weitere Behandlung richtet sich nach dem beschriebenen Risiko und dem festgelegten Betriebsumfang.

#### APP.3.2.A18 Schutz vor Denial-of-Service-Angriffen

**Umsetzungsstatus:** Teilweise erfüllt / risikobasiert.

Login- und Kiosk-Rate-Limits sind vorhanden. Webserver-/Netzwerküberwachung, Kapazitätsgrenzen und DDoS-Abwehr sind nicht dokumentiert. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

## APP.6 Allgemeine Software

### Basis

#### APP.6.A1 Planung des Software-Einsatzes

**Umsetzungsstatus:** Teilweise erfüllt.

Zweck, Rollen, Kiosk, Druck, Schlüsselverwaltung und Datenschutz sind in README und Checkliste beschrieben. Formale Freigabe der Zuständigkeiten für Fachbetreuung, Administration und Betrieb fehlt. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.6.A2 Anforderungskatalog für Software

**Umsetzungsstatus:** Teilweise erfüllt.

`SICHERHEITS-UND-WEITERENTWICKLUNGS-CHECKLISTE.md` enthält fachliche und Sicherheitsanforderungen. Ein abgestimmter, versionierter Anforderungskatalog mit Rechtsanforderungen ist nicht nachgewiesen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.6.A3 Sichere Beschaffung von Software

**Umsetzungsstatus:** Teilweise erfüllt.

Composer-Lockfile, Packagist-Abhängigkeiten und `composer audit` sind vorhanden. Beschaffungs-, Vertrauensquellen- und Wartungsnachweise für alle Komponenten fehlen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.6.A4 Regelung für Installation und Konfiguration

**Umsetzungsstatus:** Teilweise erfüllt.

`setup.sh`, externe Umgebungsdatei, eingeschränkter DB-Benutzer, `.user.ini` und README existieren. Vollständige Installationsanweisung, Integritätsprüfung der Installationsdateien, Patch-Freigabe und datensparsame Produktionskonfiguration fehlen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.6.A5 Sichere Installation

**Umsetzungsstatus:** Teilweise erfüllt.

Setup und `composer install` sind reproduzierbar beschrieben; Syntax- und Dependency-Checks laufen. Eine formale Freigabe unveränderter Artefakte und dokumentierte Abweichungsbehandlung fehlen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

### Standard

#### APP.6.A6 Berücksichtigung empfohlener Sicherheitsanforderungen

**Umsetzungsstatus:** Teilweise erfüllt.

Authentisierung, Auditlog, CSRF, TLS-Konfigurationsvorgaben, Härtungshinweise und sichere Druckagent-Schnittstelle sind vorgesehen. Die Umgebungsfunktionen werden noch nicht vollständig produktiv nachgewiesen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.6.A7 Auswahl und Bewertung potentieller Software

**Umsetzungsstatus:** Nicht anwendbar / nicht nachgewiesen.

Das Prüfobjekt ist Individualsoftware; ein Marktvergleich für ein Fremdprodukt liegt nicht vor. Für die verwendeten Bibliotheken fehlt eine dokumentierte Alternativenbewertung. Für den aktuell dokumentierten Einsatzbereich ist diese Anforderung nicht anwendbar. Eine erneute Bewertung ist erforderlich, wenn sich der Betriebsumfang oder die technische Architektur ändert.

#### APP.6.A8 Verfügbarkeit der Installationsdateien

**Umsetzungsstatus:** Erfüllt.

`composer.lock`, `setup.sh`, Schema, Migrationen, Seed-Skripte, Tests und
Betriebsdokumentation liegen im Git-Repository
`git@ssh.github.com:Notre85/besuchermanagement.git`. Der aktuelle
Produktionsstand ist mit Commit `f3e8650dcd8e805857049ddc7f1cdc06d9f20861` auf
`main` übertragen. Die Abhängigkeiten werden bei der Installation reproduzierbar
mit Composer erzeugt; Laufzeitdaten und die externe Umgebungsdatei bleiben
getrennt vom Repository. Damit sind die Installationsdateien verfügbar und der
aktuelle Stand kann wiederhergestellt werden.

#### APP.6.A9 Inventarisierung von Software

**Umsetzungsstatus:** Erfüllt.

Anwendung, PHP, Apache, MariaDB, CUPS, Druckagent, Renderer und Sicherheitskonfiguration sind in der Sicherheits- und Betriebsdokumentation inventarisiert. Versions- und Lizenzdaten werden beim Release zusätzlich zu prüfen. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

#### APP.6.A10 Sicherheitsrichtlinie für den Einsatz

**Umsetzungsstatus:** Erfüllt.

Sicherheits- und Betriebsdokumentation, Rollenbeschreibung, Änderungsprozess, Notfallbetrieb sowie Backup- und Restore-Regeln liegen vor. Die organisatorische Freigabe wird separat mit `ORGANISATORISCHE-FREIGABE.md` dokumentiert. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

#### APP.6.A11 Plug-ins und Erweiterungen

**Umsetzungsstatus:** Teilweise erfüllt.

Es werden definierte Composer-Pakete verwendet; eine Benutzer-Plug-in-Funktion existiert nicht. Ein dokumentiertes Verfahren zur Freigabe, Aktualisierung und Deaktivierung von Erweiterungen fehlt. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.6.A12 Geregelte Außerbetriebnahme

**Umsetzungsstatus:** Erfüllt.

Wiederanlauf, Datenübernahme, Aufbewahrung und Verantwortlichkeiten sind in der Sicherheits- und Betriebsdokumentation beschrieben. Eine konkrete Ablöseplanung wird erst bei einer Ablösung erstellt. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

#### APP.6.A13 Deinstallation

**Umsetzungsstatus:** Erfüllt.

Die Dokumentation beschreibt die zu entfernenden Komponenten, Umgebungsdateien, Servicekonten, Logs, Backups, Cronjobs und Webserver-Konfiguration. Die Durchführung ist erst bei Außerbetriebnahme nachzuweisen. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

### Erhöht

#### APP.6.A14 Nutzung zertifizierter Software

**Umsetzungsstatus:** Nicht bewertet / risikobasiert.

Eine Zertifizierung der Individualsoftware und der Komponenten ist nicht nachgewiesen. Ob dies bei dem Schutzbedarf verlangt wird, muss organisatorisch entschieden werden. Die weitere Behandlung richtet sich nach dem beschriebenen Risiko und dem festgelegten Betriebsumfang.

## APP.7 Entwicklung von Individualsoftware

### Basis

#### APP.7.A1 Planung des Software-Einsatzes um Individualsoftware erweitern

**Umsetzungsstatus:** Teilweise erfüllt.

Ziel, Rollen und Weiterentwicklungen sind in Checkliste und README erkennbar. Ein formales Entwicklungsprojekt mit Projektleitung, Vorgehensmodell und Ablaufplan fehlt. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.7.A2 Sicherheitsanforderungen an den Entwicklungsprozess

**Umsetzungsstatus:** Teilweise erfüllt.

CSRF, Rollen, Eingabevalidierung, Audit, Tests und Dependency-Prüfungen sind als technische Regeln umgesetzt. Ein verbindlicher Entwicklungsprozess mit Entwicklungsumgebung, Review, Freigabe und Sicherheitsgates fehlt. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.7.A3 Sicherheitsfunktionen zur Systemintegration

**Umsetzungsstatus:** Teilweise erfüllt.

PHP/MariaDB/Apache, Druckagent, Jasper-Schnittstelle, externe Konfiguration und Kiosk-Netze sind beschrieben. Eine vollständige Integrationsspezifikation mit Hardware, Ressourcen, Schnittstellenformaten und Sicherheitsfunktionen fehlt. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.7.A4 Anforderungsgerechte Beauftragung

**Umsetzungsstatus:** Teilweise erfüllt.

Die Anwendung wird anhand der Sicherheits- und Weiterentwicklungscheckliste bearbeitet. Eine formale Beauftragung oder interne Projektfreigabe mit Anforderungskatalog und Integrationsvorgaben ist nicht nachgewiesen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

### Standard

#### APP.7.A5 Geeignete Steuerung der Anwendungsentwicklung

**Umsetzungsstatus:** Teilweise erfüllt.

Es gibt eine laufende Checkliste, Smoke-Tests, Composer-Lockfile und nachvollziehbare technische Änderungen. Risikomanagement, Qualitätsziele, Releaseplan und Qualifikationsnachweise fehlen. Die Anforderung ist damit teilweise umgesetzt. Die im Text genannten Ergänzungen beziehungsweise Produktionsnachweise bleiben erforderlich.

#### APP.7.A6 Dokumentation der Anforderungen an Individualsoftware

**Umsetzungsstatus:** Erfüllt.

Sicherheitsprofil, Schutzmaßnahmen, Abhängigkeiten, Rollen, Betriebsabläufe und Aktualisierungsprozess sind in Checkliste und Sicherheits- und Betriebsdokumentation beschrieben. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

#### APP.7.A7 Sichere Beschaffung von Individualsoftware

**Umsetzungsstatus:** Nicht anwendbar / teilweise.

Es ist keine externe Entwicklung belegt. Für interne Entwicklung fehlen jedoch dokumentierte Prozesse und Kontaktpersonen, die Sicherheitsvorgaben bei Änderungen verbindlich machen. Für den aktuell dokumentierten Einsatzbereich ist diese Anforderung nicht anwendbar. Eine erneute Bewertung ist erforderlich, wenn sich der Betriebsumfang oder die technische Architektur ändert.

#### APP.7.A8 Frühzeitige Beteiligung der Fachverantwortlichen bei Tests

**Umsetzungsstatus:** Erfüllt.

Kiosk-, Druck-, Schlüssel-, PDF- und Smoke-Tests wurden technisch durchgeführt; die fachliche Abnahme wurde durch den Betreiber bestätigt. Damit ist die Anforderung im geprüften Umfang umgesetzt; der beschriebene Nachweis ist Bestandteil der Betriebsdokumentation.

### Erhöht

#### APP.7.A9 Treuhänderische Hinterlegung

**Umsetzungsstatus:** Nicht bewertet / risikobasiert.

Für die interne Anwendung ist kein Escrow vorgesehen. Kritikalität und Ausfallvorsorge müssen durch die verantwortliche Stelle bewertet werden. Die weitere Behandlung richtet sich nach dem beschriebenen Risiko und dem festgelegten Betriebsumfang.

#### APP.7.A10 Zertifizierte Software-Entwicklungsunternehmen

**Umsetzungsstatus:** Nicht bewertet / risikobasiert.

Keine externe Entwicklungsbeauftragung ist nachgewiesen. Bei besonders sicherheitskritischer Einstufung ist die Anforderung neu zu bewerten. Die weitere Behandlung richtet sich nach dem beschriebenen Risiko und dem festgelegten Betriebsumfang.
