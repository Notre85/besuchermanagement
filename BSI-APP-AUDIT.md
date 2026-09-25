# BSI-Prüfung der Anwendung

Stand der Prüfung: **21.09.2026**
Prüfobjekt: `/var/www/besuchermanagement`
Prüfart: statische Code-, Konfigurations- und Dokumentationsprüfung mit ergänzenden Syntax-, Dependency-, HTTP- und Datenbanktests.

Die Bewertung ist kein BSI-Zertifikat und kein vollständiger IT-Grundschutz-Check. Für Anforderungen an die Produktionsumgebung wurden nur die im Arbeitsverzeichnis vorhandenen Nachweise bewertet. Die ausführliche Beschreibung der Umsetzung steht in der [Sicherheits- und Betriebsdokumentation](SICHERHEITSBETRIEBS-DOKUMENTATION.md). Firewall, TLS-Zertifikate, Apache-Laufzeitkonfiguration, Betriebssystem, organisatorische Zuständigkeiten und physische Kioskgeräte müssen zusätzlich vor Ort geprüft werden.

Die ausformulierten Feststellungen zu jeder einzelnen Anforderung stehen in
den [ausformulierten BSI-Beschreibungen](BSI-APP-AUSFORMULIERTE-BESCHREIBUNGEN.md).
Dieses Dokument bleibt die kompakte Statusübersicht.

## Bewertungslegende

| Bewertung | Bedeutung |
|---|---|
| Erfüllt | Im Prüfobjekt ist eine passende technische oder dokumentierte Umsetzung nachweisbar. |
| Teilweise erfüllt | Ein Teil ist umgesetzt; mindestens ein wesentlicher Nachweis oder eine Ergänzung fehlt. |
| Offen | Keine ausreichende Umsetzung oder kein belastbarer Nachweis gefunden. |
| Nicht anwendbar | Die Anforderung trifft auf den betrachteten Einsatz derzeit nicht zu; die Begründung ist angegeben. |

## Zusammenfassung

Die Anwendung verfügt über zentrale Authentisierung und Rollenprüfung, CSRF-Schutz, POST-only-Schreibaktionen, serverseitige Eingabevalidierung, PDO-Prepared-Statements, Passwort-Hashing, lokale Frontend-Abhängigkeiten, Security-Header, externe Konfiguration, Auditlog, Rate-Limits, Backups außerhalb des Webroots sowie reproduzierbare Composer-Abhängigkeiten. HTTP-Sicherheitstests sind in `tests/http_security.sh` automatisiert.

Für eine belastbare produktive Freigabe bleiben insbesondere diese Punkte offen:

- HTTPS im internen Netz einschließlich HTTP-zu-HTTPS-Weiterleitung und HSTS ist nicht aktiviert; die Abweichung ist in `RISIKOANALYSE-INTERNES-HTTPS.md` bewertet.
- Eindeutige Authentisierung der Kioskgeräte zusätzlich zur Netz-Whitelist, etwa mTLS oder ein aktivierter Geräte-Token.
- Aktivierung und Betrieb des optionalen Kiosk-Gerätetokens; eine Besucher-ID allein ist erratbar.
- Vollständige Apache-Härtung und Nachweis der Server-, Fehler- und Integritätsprotokollierung.
- Regelmäßige Sicherheitsrevisionen, Penetrationstests und dokumentierte Abweichungsbearbeitung.
- Organisatorische Freigabe der Sicherheits- und Betriebsdokumentation sowie Datenschutznachweise.

## APP.3.1 Webanwendungen und Webservices

Bewertungsgrundlage: [BSI APP.3.1 Webanwendungen und Webservices, Edition 2022](https://www.bsi.bund.de/SharedDocs/Downloads/DE/BSI/Grundschutz/IT-GS-Kompendium_Einzel_PDFs_2022/06_APP_Anwendungen/APP_3_1_Webanwendungen_und_Webservices_Edition_2022.pdf?__blob=publicationFile&v=4).

Bewertung nach Anforderungsstufe: **B = Basis**, **S = Standard**, **H = Erhöht**.

### Basis
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.3.1.A1 Authentisierung (B) | **Teilweise erfüllt** | Verwaltungsfunktionen prüfen Login und Rolle über `controllers/BaseController.php`; Login nutzt `password_verify()` und Login-Rate-Limit. Der Kiosk erlaubt bewusst anonyme Check-ins aus freigegebenen Netzen. Auswahl und Dokumentation der angemessenen Authentisierungsmethode fehlen. |
| APP.3.1.A4 Kontrolliertes Einbinden von Dateien und Inhalten (B) | **Nicht anwendbar** | Es gibt keine Benutzer-Datei-Upload-Funktion. Vorlagen werden als Text über eine Admin-POST-Funktion gespeichert; zulässige Variablen und gefährliche HTML-Elemente werden serverseitig begrenzt. |
| APP.3.1.A7 Schutz vor unerlaubter automatisierter Nutzung (B) | **Teilweise erfüllt** | Login- und Kiosk-Rate-Limits nach IP, Sitzung und Gerätekennung, Netzwerk-Whitelist, CSRF und Auditlog sind vorhanden. Ein Geräte-Token ist optional vorbereitet, aber noch nicht produktiv aktiviert; die Besucher-ID allein bleibt erratbar. |
| APP.3.1.A14 Schutz vertraulicher Daten (B) | **Teilweise erfüllt** | Passwörter werden mit `password_hash()` gespeichert; `.env` liegt außerhalb des Webroots, Quellverzeichnisse werden per `.htaccess` gesperrt und die Produktionsrechte wurden auf der VM korrigiert. Der bewusste Verzicht auf HTTPS bleibt als Risiko akzeptiert. |

### Standard
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.3.1.A8 Systemarchitektur [Beschaffungsstelle] (S) | **Teilweise erfüllt** | MVC-Struktur, getrennte Controller/Services, externe Konfiguration, Datenbank- und Druckagent-Schnittstellen sind erkennbar. Ein formales Architektur- und Schutzbedarfsdokument mit Verantwortlichen fehlt. |
| APP.3.1.A9 Beschaffung von Webanwendungen und Webservices (S) | **Teilweise erfüllt** | Für die Eigenentwicklung bestehen Checkliste, `composer.lock`, Dependency-Audit und Sicherheitsfunktionen. Ein formaler Anforderungskatalog einschließlich Wartungs- und Sicherheitszusagen für externe Komponenten ist nicht nachgewiesen. |
| APP.3.1.A11 Sichere Anbindung von Hintergrundsystemen (S) | **Teilweise erfüllt** | MariaDB wird über PDO angesprochen; Jasper und der Druckagent verwenden definierte Schnittstellen und Token. TLS für alle Netzgrenzen sowie eine vollständige Freigabe- und Netzmatrix sind nicht nachgewiesen. |
| APP.3.1.A12 Sichere Konfiguration (S) | **Teilweise erfüllt** | POST-only-Schreibaktionen, generische Fehler, externe Umgebungsdatei, Limits und Router-Whitelist sind vorhanden. Eine vollständige Abschaltung unnötiger HTTP-Methoden und die produktive Webserver-Konfiguration fehlen. |
| APP.3.1.A21 Sichere HTTP-Konfiguration bei Webanwendungen (S) | **Teilweise erfüllt** | CSP ohne externe CDN-Quellen, Cache-Control no-store, X-Content-Type-Options, X-Frame-Options, Referrer-Policy und Cookie-Attribute sind vorhanden. HSTS und eine konsequente HTTPS-Erzwingung fehlen bewusst weiterhin. |
| APP.3.1.A22 Penetrationstest und Revision (S) | **Offen** | Smoke-Tests, Syntaxprüfung und `composer audit` sind vorhanden. Ein regelmäßiger Penetrationstest, Revisionsplan, Ergebnisnachweis und ISB-Prozess sind nicht dokumentiert. |

### Erhöht
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.3.1.A20 Einsatz von Web Application Firewalls (H) | **Offen / risikobasiert** | Keine WAF ist dokumentiert. Für das interne Netz ist eine WAF eine Maßnahme bei erhöhtem Schutzbedarf; eine Risikoentscheidung dazu fehlt. |

## APP.3.2 Webserver

Bewertungsgrundlage: [BSI APP.3.2 Webserver, Edition 2023](https://www.bsi.bund.de/SharedDocs/Downloads/DE/BSI/Grundschutz/IT-GS-Kompendium_Einzel_PDFs_2023/06_APP_Anwendungen/APP_3_2_Webserver_Edition_2023.pdf?__blob=publicationFile&v=3).

Bewertung nach Anforderungsstufe: **B = Basis**, **S = Standard**, **H = Erhöht**.

### Basis
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.3.2.A1 Sichere Konfiguration eines Webservers (B) | **Teilweise erfüllt** | `headers`, deaktivierte Directory-Listings, `ServerTokens Prod`, `ServerSignature Off` und `TraceEnable Off` sind auf der VM aktiv. Quellcodebesitz und Schreibrechte sind korrigiert; die Prüfung nicht benötigter Module und der Prozessisolation bleibt als Betriebsnachweis offen. |
| APP.3.2.A2 Schutz der Webserver-Dateien (B) | **Erfüllt** | `.htaccess` sperrt Konfigurations-, Quell-, Log- und Vendor-Verzeichnisse; `.env` liegt außerhalb des Webroots; die VM verwendet `Options -Indexes`; der Quellcode gehört `root:root`, nur Logs sind für `www-data` beschreibbar. |
| APP.3.2.A3 Absicherung von Datei-Uploads und -Downloads (B) | **Teilweise erfüllt** | Es gibt keine Benutzer-Uploads. Berichte, PDFs und Backups sind jedoch Download-/Ausgabefunktionen und müssen im laufenden Apache einschließlich Größen-, Rechte- und Malware-Konzept geprüft werden. |
| APP.3.2.A4 Protokollierung von Ereignissen (B) | **Teilweise erfüllt** | Apache-Access-/Error-Logs sind im Setup vorgesehen; die Anwendung schreibt Monolog- und Auditlog-Einträge. Regelmäßige Auswertung, zentrale Aufbewahrung und Alarmierung sind nicht belegt. |
| APP.3.2.A5 Authentisierung (B) | **Erfüllt** | Anwendungspasswörter werden mit `password_hash()` gespeichert und über `password_verify()` geprüft. Die Passwortdatei ist die Datenbank und wird über den eingeschränkten Laufzeitbenutzer angesprochen. |
| APP.3.2.A7 Rechtliche Rahmenbedingungen für Webangebote (B) | **Teilweise erfüllt** | Ein Datenschutzkonzept mit Datenkategorien, Rollen, Aufbewahrung, Betroffenenrechten, Vorfällen und Verantwortlichkeitsfeldern liegt vor. Die rechtliche Prüfung, Fristfestlegung und organisatorische Freigabe stehen noch aus. |
| APP.3.2.A11 Verschlüsselung über TLS (B) | **Teilweise erfüllt / Risikoakzeptanz** | HTTPS ist bewusst nicht aktiviert. Die Abweichung, Schutzbedarf, Randbedingungen, kompensierende Maßnahmen und Auslöser für eine Neubewertung sind in `RISIKOANALYSE-INTERNES-HTTPS.md` dokumentiert. |

### Standard
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.3.2.A8 Planung des Einsatzes eines Webservers (S) | **Teilweise erfüllt** | README beschreibt Webserver, Kiosk, Druckagent und Betriebswege. Zielgruppen, Webserver-Verantwortliche, vollständige Inhalte-/Schnittstellenübersicht und Freigabeprozess fehlen. |
| APP.3.2.A9 Sicherheitsrichtlinie für den Webserver (S) | **Erfüllt** | Härtung, Dateischutz, Protokollierung, Änderungsverfahren, Wiederanlauf und offene Produktionsnachweise sind in `SICHERHEITSBETRIEBS-DOKUMENTATION.md` beschrieben. Die fachliche Freigabe der Richtlinie ist organisatorisch noch einzuholen. |
| APP.3.2.A10 Auswahl eines geeigneten Webhosters (S) | **Nicht anwendbar** | Die Anwendung ist für eine selbst betriebene interne VM vorgesehen; ein externer Webhoster ist nicht nachgewiesen. Bei späterem Hosting ist die Anforderung neu zu bewerten. |
| APP.3.2.A12 Geeigneter Umgang mit Fehlern und Fehlermeldungen (S) | **Teilweise erfüllt** | `display_errors` ist deaktiviert, PHP-Version wird nicht exponiert, die Anwendung verwendet generische Fehlertexte und das Setup setzt die Server-Signatur ab. Apache-Fehlerseiten und eindeutige Fehlerkorrelation sind nicht vollständig konfiguriert. |
| APP.3.2.A13 Zugriffskontrolle für Webcrawler (S) | **Teilweise erfüllt** | `robots.txt` sperrt kooperative Crawler; die Anwendung ist intern und der Kiosk hat eine Netz-Whitelist. Gegen nicht kooperative Crawler besteht kein eigenständiger Schutz. |
| APP.3.2.A14 Integritätsprüfungen und Schutz vor Schadsoftware (S) | **Offen** | Composer-Abhängigkeiten werden auditiert. Regelmäßige Integritätsprüfungen des Webroots, Malware-Prüfungen, Signatur-/Hashkontrollen und Alarmierung sind nicht dokumentiert. |
| APP.3.2.A16 Penetrationstest und Revision (S) | **Teilweise erfüllt** | Der Penetrationstest ist für den nächsten Sicherheitszyklus vorgesehen. Bis dahin bestehen automatisierte Syntax-, Dependency- und HTTP-Sicherheitstests. |
| APP.3.2.A20 Benennung von Anzusprechenden (S) | **Erfüllt** | Rollen sind dokumentiert. Der Sicherheitskontakt `mailto:iuk.ilsmfrs@brk.de` ist in `security.txt` und `/.well-known/security.txt` hinterlegt. |

### Erhöht
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.3.2.A15 Redundanz (H) | **Nicht bewertet / risikobasiert** | Für eine einzelne interne VM ist keine Redundanz implementiert. Die Notwendigkeit muss aus Verfügbarkeitsanforderungen und Schutzbedarf entschieden werden. |
| APP.3.2.A18 Schutz vor Denial-of-Service-Angriffen (H) | **Teilweise erfüllt / risikobasiert** | Login- und Kiosk-Rate-Limits sind vorhanden. Webserver-/Netzwerküberwachung, Kapazitätsgrenzen und DDoS-Abwehr sind nicht dokumentiert. |

## APP.6 Allgemeine Software

Bewertungsgrundlage: [BSI APP.6 Allgemeine Software, Edition 2022](https://www.bsi.bund.de/SharedDocs/Downloads/DE/BSI/Grundschutz/IT-GS-Kompendium_Einzel_PDFs_2022/06_APP_Anwendungen/APP_6_Allgemeine_Software_Edition_2022.pdf?__blob=publicationFile&v=3).

Bewertung nach Anforderungsstufe: **B = Basis**, **S = Standard**, **H = Erhöht**.

### Basis
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.6.A1 Planung des Software-Einsatzes (B) | **Teilweise erfüllt** | Zweck, Rollen, Kiosk, Druck, Schlüsselverwaltung und Datenschutz sind in README und Checkliste beschrieben. Formale Freigabe der Zuständigkeiten für Fachbetreuung, Administration und Betrieb fehlt. |
| APP.6.A2 Anforderungskatalog für Software (B) | **Teilweise erfüllt** | `SICHERHEITS-UND-WEITERENTWICKLUNGS-CHECKLISTE.md` enthält fachliche und Sicherheitsanforderungen. Ein abgestimmter, versionierter Anforderungskatalog mit Rechtsanforderungen ist nicht nachgewiesen. |
| APP.6.A3 Sichere Beschaffung von Software (B) | **Teilweise erfüllt** | Composer-Lockfile, Packagist-Abhängigkeiten und `composer audit` sind vorhanden. Beschaffungs-, Vertrauensquellen- und Wartungsnachweise für alle Komponenten fehlen. |
| APP.6.A4 Regelung für Installation und Konfiguration (B) | **Teilweise erfüllt** | `setup.sh`, externe Umgebungsdatei, eingeschränkter DB-Benutzer, `.user.ini` und README existieren. Vollständige Installationsanweisung, Integritätsprüfung der Installationsdateien, Patch-Freigabe und datensparsame Produktionskonfiguration fehlen. |
| APP.6.A5 Sichere Installation (B) | **Teilweise erfüllt** | Setup und `composer install` sind reproduzierbar beschrieben; Syntax- und Dependency-Checks laufen. Eine formale Freigabe unveränderter Artefakte und dokumentierte Abweichungsbehandlung fehlen. |

### Standard
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.6.A6 Berücksichtigung empfohlener Sicherheitsanforderungen (S) | **Teilweise erfüllt** | Authentisierung, Auditlog, CSRF, TLS-Konfigurationsvorgaben, Härtungshinweise und sichere Druckagent-Schnittstelle sind vorgesehen. Die Umgebungsfunktionen werden noch nicht vollständig produktiv nachgewiesen. |
| APP.6.A7 Auswahl und Bewertung potentieller Software (S) | **Nicht anwendbar / nicht nachgewiesen** | Das Prüfobjekt ist Individualsoftware; ein Marktvergleich für ein Fremdprodukt liegt nicht vor. Für die verwendeten Bibliotheken fehlt eine dokumentierte Alternativenbewertung. |
| APP.6.A8 Verfügbarkeit der Installationsdateien (S) | **Erfüllt** | `composer.lock`, `setup.sh`, Schema, Migrationen, Seed-Skripte, Tests und Betriebsdokumentation liegen im Git-Repository `git@ssh.github.com:Notre85/besuchermanagement.git`. Der aktuelle Produktionsstand ist mit Commit `f3e8650dcd8e805857049ddc7f1cdc06d9f20861` auf `main` übertragen. Die Abhängigkeiten werden bei der Installation reproduzierbar mit Composer erzeugt; Laufzeitdaten und die externe Umgebungsdatei bleiben getrennt vom Repository. |
| APP.6.A9 Inventarisierung von Software (S) | **Erfüllt** | Anwendung, PHP, Apache, MariaDB, CUPS, Druckagent, Renderer und Sicherheitskonfiguration sind in der Sicherheits- und Betriebsdokumentation inventarisiert. Versions- und Lizenzdaten werden beim Release zusätzlich zu prüfen. |
| APP.6.A10 Sicherheitsrichtlinie für den Einsatz (S) | **Erfüllt** | Sicherheits- und Betriebsdokumentation, Rollenbeschreibung, Änderungsprozess, Notfallbetrieb sowie Backup- und Restore-Regeln liegen vor. Die organisatorische Freigabe wird separat mit `ORGANISATORISCHE-FREIGABE.md` dokumentiert. |
| APP.6.A11 Plug-ins und Erweiterungen (S) | **Teilweise erfüllt** | Es werden definierte Composer-Pakete verwendet; eine Benutzer-Plug-in-Funktion existiert nicht. Ein dokumentiertes Verfahren zur Freigabe, Aktualisierung und Deaktivierung von Erweiterungen fehlt. |
| APP.6.A12 Geregelte Außerbetriebnahme (S) | **Erfüllt** | Wiederanlauf, Datenübernahme, Aufbewahrung und Verantwortlichkeiten sind in der Sicherheits- und Betriebsdokumentation beschrieben. Eine konkrete Ablöseplanung wird erst bei einer Ablösung erstellt. |
| APP.6.A13 Deinstallation (S) | **Erfüllt** | Die Dokumentation beschreibt die zu entfernenden Komponenten, Umgebungsdateien, Servicekonten, Logs, Backups, Cronjobs und Webserver-Konfiguration. Die Durchführung ist erst bei Außerbetriebnahme nachzuweisen. |

### Erhöht
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.6.A14 Nutzung zertifizierter Software (H) | **Nicht bewertet / risikobasiert** | Eine Zertifizierung der Individualsoftware und der Komponenten ist nicht nachgewiesen. Ob dies bei dem Schutzbedarf verlangt wird, muss organisatorisch entschieden werden. |

## APP.7 Entwicklung von Individualsoftware

Bewertungsgrundlage: [BSI APP.7 Entwicklung von Individualsoftware, Edition 2023](https://www.bsi.bund.de/SharedDocs/Downloads/DE/BSI/Grundschutz/IT-GS-Kompendium_Einzel_PDFs_2023/06_APP_Anwendungen/APP_7_Entwicklung_von_Individualsoftware_Edition_2023.pdf?__blob=publicationFile&v=3).

Bewertung nach Anforderungsstufe: **B = Basis**, **S = Standard**, **H = Erhöht**.

### Basis
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.7.A1 Planung des Software-Einsatzes um Individualsoftware erweitern (B) | **Teilweise erfüllt** | Ziel, Rollen und Weiterentwicklungen sind in Checkliste und README erkennbar. Ein formales Entwicklungsprojekt mit Projektleitung, Vorgehensmodell und Ablaufplan fehlt. |
| APP.7.A2 Sicherheitsanforderungen an den Entwicklungsprozess (B) | **Teilweise erfüllt** | CSRF, Rollen, Eingabevalidierung, Audit, Tests und Dependency-Prüfungen sind als technische Regeln umgesetzt. Ein verbindlicher Entwicklungsprozess mit Entwicklungsumgebung, Review, Freigabe und Sicherheitsgates fehlt. |
| APP.7.A3 Sicherheitsfunktionen zur Systemintegration (B) | **Teilweise erfüllt** | PHP/MariaDB/Apache, Druckagent, Jasper-Schnittstelle, externe Konfiguration und Kiosk-Netze sind beschrieben. Eine vollständige Integrationsspezifikation mit Hardware, Ressourcen, Schnittstellenformaten und Sicherheitsfunktionen fehlt. |
| APP.7.A4 Anforderungsgerechte Beauftragung (B) | **Teilweise erfüllt** | Die Anwendung wird anhand der Sicherheits- und Weiterentwicklungscheckliste bearbeitet. Eine formale Beauftragung oder interne Projektfreigabe mit Anforderungskatalog und Integrationsvorgaben ist nicht nachgewiesen. |

### Standard
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.7.A5 Geeignete Steuerung der Anwendungsentwicklung (S) | **Teilweise erfüllt** | Es gibt eine laufende Checkliste, Smoke-Tests, Composer-Lockfile und nachvollziehbare technische Änderungen. Risikomanagement, Qualitätsziele, Releaseplan und Qualifikationsnachweise fehlen. |
| APP.7.A6 Dokumentation der Anforderungen an Individualsoftware (S) | **Erfüllt** | Sicherheitsprofil, Schutzmaßnahmen, Abhängigkeiten, Rollen, Betriebsabläufe und Aktualisierungsprozess sind in Checkliste und Sicherheits- und Betriebsdokumentation beschrieben. |
| APP.7.A7 Sichere Beschaffung von Individualsoftware (S) | **Nicht anwendbar / teilweise** | Es ist keine externe Entwicklung belegt. Für interne Entwicklung fehlen jedoch dokumentierte Prozesse und Kontaktpersonen, die Sicherheitsvorgaben bei Änderungen verbindlich machen. |
| APP.7.A8 Frühzeitige Beteiligung der Fachverantwortlichen bei Tests (S) | **Erfüllt** | Kiosk-, Druck-, Schlüssel-, PDF- und Smoke-Tests wurden technisch durchgeführt; die fachliche Abnahme wurde durch den Betreiber bestätigt. |

### Erhöht
| Anforderung | Antwort / Bewertung | Nachweis und Feststellung |
|---|---|---|
| APP.7.A9 Treuhänderische Hinterlegung (H) | **Nicht bewertet / risikobasiert** | Für die interne Anwendung ist kein Escrow vorgesehen. Kritikalität und Ausfallvorsorge müssen durch die verantwortliche Stelle bewertet werden. |
| APP.7.A10 Zertifizierte Software-Entwicklungsunternehmen (H) | **Nicht bewertet / risikobasiert** | Keine externe Entwicklungsbeauftragung ist nachgewiesen. Bei besonders sicherheitskritischer Einstufung ist die Anforderung neu zu bewerten. |
## Technische Prüfungen

Zum Prüfzeitpunkt wurden erfolgreich ausgeführt:

- `php -l` für die PHP-Anwendungsdateien.
- `bash tests/smoke.sh`.
- `composer validate --no-check-publish`.
- `composer audit --format=plain` ohne bekannte Sicherheitswarnungen.
- `apachectl -t` mit `Syntax OK`.
- Datenbankverbindung und Abruf von Auditlog, Schlüsselhistorie und Druckvorlagen.
- HTTP-Sicherheitsprüfungen für Login, Kiosk, geschützte Endpunkte, Cache-Control, Schutzheader und fehlenden CSRF-Token.

Diese Tests belegen die geprüften Funktionen zum Prüfzeitpunkt. Sie ersetzen weder einen Produktionskonfigurations-Check noch einen Penetrationstest oder die organisatorische Abnahme.

## Maßnahmenpriorität

1. Organisatorische Freigabe und Datenschutzkonzept ausfüllen und freigeben.
2. Kiosk-Gerätetoken fachlich entscheiden; die technische Aktivierung bleibt bewusst zurückgestellt.
3. Rollen-, CSRF-, XSS-, Parallelitäts-, Session- und Datenschutztests als reproduzierbare Testsuite ergänzen.
4. Penetrationstest im nächsten Sicherheitszyklus durchführen und das Ergebnis dokumentieren.
5. Softwareinventar, Release-/Rollback-Verfahren, Außerbetriebnahme und Notfallbetrieb regelmäßig pflegen.
6. HTTPS bei Änderungen des Netzes, der Endgeräte oder des Schutzbedarfs neu bewerten; aktuell ist das Restrisiko akzeptiert.

## Quellen

- [BSI IT-Grundschutz-Kompendium](https://www.bsi.bund.de/DE/Themen/Unternehmen-und-Organisationen/Standards-und-Zertifizierung/IT-Grundschutz/it-grundschutz_node.html)
- [APP.3.1 Webanwendungen und Webservices, Edition 2022](https://www.bsi.bund.de/SharedDocs/Downloads/DE/BSI/Grundschutz/IT-GS-Kompendium_Einzel_PDFs_2022/06_APP_Anwendungen/APP_3_1_Webanwendungen_und_Webservices_Edition_2022.pdf?__blob=publicationFile&v=4)
- [APP.3.2 Webserver, Edition 2023](https://www.bsi.bund.de/SharedDocs/Downloads/DE/BSI/Grundschutz/IT-GS-Kompendium_Einzel_PDFs_2023/06_APP_Anwendungen/APP_3_2_Webserver_Edition_2023.pdf?__blob=publicationFile&v=3)
- [APP.6 Allgemeine Software, Edition 2022](https://www.bsi.bund.de/SharedDocs/Downloads/DE/BSI/Grundschutz/IT-GS-Kompendium_Einzel_PDFs_2022/06_APP_Anwendungen/APP_6_Allgemeine_Software_Edition_2022.pdf?__blob=publicationFile&v=3)
- [APP.7 Entwicklung von Individualsoftware, Edition 2023](https://www.bsi.bund.de/SharedDocs/Downloads/DE/BSI/Grundschutz/IT-GS-Kompendium_Einzel_PDFs_2023/06_APP_Anwendungen/APP_7_Entwicklung_von_Individualsoftware_Edition_2023.pdf?__blob=publicationFile&v=3)
