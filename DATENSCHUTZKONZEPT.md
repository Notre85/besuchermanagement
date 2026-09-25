# Datenschutzkonzept für das Besuchermanagement

Stand: **25.09.2026**
Geltungsbereich: interne VM und angeschlossene Kiosk-, Druck- und
Administrationskomponenten

Dieses Konzept beschreibt die Verarbeitung personenbezogener Daten. Die
verantwortliche Stelle und gegebenenfalls der Datenschutzbeauftragte prüfen vor
der organisatorischen Freigabe die Rechtsgrundlage, die konkreten Fristen und
die erforderlichen Informationspflichten.

## Verantwortlichkeit und Zweck

| Feld | Eintrag |
|---|---|
| Verantwortliche Stelle | ______________________________ |
| Datenschutzbeauftragte Person | ______________________________ |
| Fachlich verantwortliche Person | ______________________________ |
| Technisch verantwortliche Person | ______________________________ |
| Zweck | Besucher anmelden, Anwesenheit dokumentieren, Schlüssel ausgeben und zurücknehmen, Ausweise und erforderliche Druckausgaben erstellen |
| Betriebsort | eigene interne VM |

Die Anwendung wird ausschließlich für Zutritts- und Empfangsorganisation,
Nachvollziehbarkeit von Schlüsselübergaben und die dazu erforderlichen
Betriebs- und Sicherheitsnachweise verwendet. Eine Nutzung für eine allgemeine
Leistungs- oder Verhaltenskontrolle ist ausgeschlossen.

## Verarbeitete Daten

| Datenkategorie | Beispiele | Zweck |
|---|---|---|
| Besucherstammdaten | Name, Firma, Besucher-ID, Kontaktangaben | eindeutige Zuordnung und Vorbereitung eines Besuchs |
| Besuchsdaten | Gastgeber, Abteilung, Besuchsgrund, Standort, Beginn, Ende, Status | Terminplanung, Check-in und Check-out |
| Schlüsseldaten | Inventarnummer, Ausgabe, Rückgabe, verantwortlicher Benutzer | Nachweis der Schlüsselverantwortung |
| Ausweis- und Druckdaten | Name, Gastgeber, Gültigkeit, QR-Code, Druckstatus | Besucherausweis, Schlüssel-BON und Label |
| Kontodaten | Benutzername, Name, Rolle, Passwort-Hash | Anmeldung und Berechtigung der Beschäftigten |
| Protokolldaten | Aktion, Zeitpunkt, Ergebnis, Benutzer, IP, Gerätekennung, Metadaten | Sicherheits- und Nachweiszwecke |
| Technische Betriebsdaten | Apache-Logs, Fehlerlogs, Druckjobstatus | Betrieb, Fehleranalyse und Schutz der Anwendung |

Passwörter werden nicht im Klartext gespeichert. Tokens, Passwörter und unnötige
personenbezogene Rohdaten werden nicht in das Auditlog geschrieben.

## Rechtsgrundlage und Informationspflicht

Die verantwortliche Stelle trägt vor dem Regelbetrieb je Verarbeitung die
zutreffende Rechtsgrundlage ein. Mögliche Kategorien sind je nach Organisation
beispielsweise gesetzliche Pflichten, berechtigtes Interesse an einem sicheren
Besuchermanagement oder eine ausdrücklich erforderliche Einwilligung. Die
Rechtsgrundlage darf nicht pauschal aus dieser technischen Dokumentation
abgeleitet werden.

Besucher erhalten am Empfang beziehungsweise im Kiosk einen kurzen
Datenschutzhinweis mit Zweck, Verantwortlicher Stelle, Kontakt des
Datenschutzes, Speicherdauer und Betroffenenrechten. Der vollständige Hinweis
wird an der dafür vorgesehenen internen Stelle bereitgestellt.

## Zugriff und Zweckbindung

- Empfang sieht nur die für Check-in und Check-out erforderlichen Daten.
- Berichtersteller erhalten die freigegebenen Berichte und Exporte.
- Manager verwalten Besucher und Besuche.
- Admin und Superadmin verwalten System, Benutzer, Drucker, Vorlagen und Backups.

Besuchshistorien und Exporte werden auditierbar protokolliert. Schlüsselausgaben
werden dem konkreten Besucher, Schlüssel und verantwortlichen Benutzer
zugeordnet. Der Kiosk zeigt keine vollständige Besuchshistorie an.

## Aufbewahrung und Löschung

| Datenart | Vorgesehene Behandlung | Frist/Entscheidung |
|---|---|---|
| Aktive Besuchsdaten | für Empfang und Sicherheitsnachweis verfügbar | ______ Tage/Monate |
| Abgeschlossene Besuche | löschen oder anonymisieren | `RETENTION_DAYS=______` |
| Auditdaten | nur für Nachweis und Sicherheitsaufklärung | `RETENTION_DAYS=______` |
| Druckjobdaten | nach erfolgreicher Ausgabe und Fehlerklärung löschen oder begrenzen | ______ Tage |
| Backups | rotieren und nach Ablauf sicher löschen | `BACKUP_RETENTION_DAYS=______` |
| Apache-/Anwendungslogs | zweckgebunden aufbewahren | ______ Tage |

Die Anwendung unterstützt die Anonymisierung alter Besuchsdaten und die
Löschung alter Auditdaten über `bin/retention.php`. Backups und Logs müssen in
die gleiche Lösch- und Aufbewahrungsentscheidung einbezogen werden.

## Betroffenenrechte und Vorfälle

Anfragen auf Auskunft, Berichtigung, Löschung, Einschränkung, Widerspruch und
gegebenenfalls Datenübertragbarkeit werden von der verantwortlichen Stelle
bearbeitet. Die Identität der anfragenden Person ist angemessen zu prüfen.

Ein Verlust, eine unbefugte Offenlegung oder Manipulation von Besucher- oder
Schlüsseldaten wird unverzüglich an die verantwortliche Stelle und den
Datenschutzkontakt gemeldet. Zugangsdaten und Gerätetokens werden bei Verdacht
geändert; Audit- und Apache-Logs werden gesichert.

| Zuständigkeit | Kontakt/Verfahren |
|---|---|
| Datenschutzanfragen | ______________________________ |
| Fristüberwachung | ______________________________ |
| Datenschutzvorfälle | ______________________________ |
| Löschung/Anonymisierung | ______________________________ |

## Technische und organisatorische Maßnahmen

- Anmeldung und Rollenprüfung für Verwaltungsfunktionen
- CSRF-Schutz und POST-only-Schreibaktionen
- serverseitige Validierung und kontextbezogenes Escaping
- Session-Cookies mit `HttpOnly` und `SameSite`
- interne Netzbegrenzung des Kiosks und Rate-Limits
- Auditlog für fachliche und sicherheitsrelevante Aktionen
- Backups außerhalb des Webroots mit restriktiven Rechten
- Quellcode nicht durch `www-data` beschreibbar; nur Logs sind Laufzeitdaten
- Schutz sensibler Dateien durch Apache
- dokumentierter Notfall- und Wiederanlaufbetrieb

Der bewusste Verzicht auf internes HTTPS ist in einer separaten
[Risikoanalyse](RISIKOANALYSE-INTERNES-HTTPS.md) bewertet und darf nicht als
Verschlüsselung personenbezogener Daten auf dem Transportweg verstanden werden.

## Auftragsverarbeitung und Freigabe

Die Anwendung ist für den Eigenbetrieb vorgesehen. Externe Empfänger oder
Auftragsverarbeiter sind derzeit nicht vorgesehen. Bei Hosting, Support,
Mailversand, Druckdienst oder Fernwartung durch Dritte sind Zweck, Datenumfang,
Zugriff, Vertrag und gegebenenfalls Drittlandbezug separat zu prüfen.

Vor dem Produktivbetrieb ergänzt die verantwortliche Stelle die offenen Felder,
bestätigt Rechtsgrundlage und Fristen und gibt dieses Konzept zusammen mit der
[organisatorischen Freigabe](ORGANISATORISCHE-FREIGABE.md) frei.

| Funktion | Name | Datum | Unterschrift |
|---|---|---|---|
| Verantwortliche Stelle | __________________ | __________ | __________________ |
| Datenschutz | __________________ | __________ | __________________ |
| IT-Administration | __________________ | __________ | __________________ |
