# Besuchermanagement: Sicherheits- und Weiterentwicklungsplan

Diese Checkliste ist die Arbeitsgrundlage für die Härtung und Weiterentwicklung der Anwendung. Die Reihenfolge ist verbindlich: Erst werden Zugriffsschutz, Datenabfluss und Datenintegrität abgesichert; danach folgen neue Produktfunktionen.

## 0. Vorbereitung und Leitplanken

- [ ] Arbeitskopie und Datenbank-Backup vor Beginn der Umsetzung erstellen
- [ ] Bestehende Änderungen im Arbeitsverzeichnis prüfen und eindeutig vom neuen Arbeitsstand trennen
- [ ] Testdatenbank und Testbenutzer für alle Rollen anlegen
- [x] Rollen und erlaubte Aktionen fachlich festlegen:
  - [x] Empfang/Kiosk: Check-in und Check-out
  - [x] Berichtersteller: Berichte lesen und exportieren
  - [x] Manager: Besucher und Besuche verwalten
  - [x] Admin: Benutzer, Systemeinstellungen und Backups verwalten
  - [x] Superadmin: technische Gesamtadministration
- [x] Festlegen, ob der Check-in ohne persönliches Benutzerkonto ausschließlich im isolierten Kiosk-Modus erlaubt bleibt

## 1. Kritische Sicherheitslücken

- [x] Zentrale Funktionen für `requireLogin`, Rollenprüfung und einheitliche Fehlerantworten einführen
- [x] Login-, Rollen- und CSRF-Prüfung direkt in jede schreibende Controller-Methode aufnehmen
- [x] Direkten Webzugriff auf Controller-Dateien unterbinden; Aktionen ausschließlich über definierte Einstiegspunkte routen
- [x] Besucher ändern und löschen nur für Manager, Admin und Superadmin erlauben
- [x] Benutzer anlegen, ändern und löschen nur für Admin und Superadmin erlauben
- [x] Selbst-Herabstufung oder Löschung des letzten Superadmins verhindern
- [x] Check-in und Check-out auf POST umstellen
- [x] CSRF-Schutz für alle schreibenden Aktionen durchsetzen
- [x] Logout auf POST umstellen und CSRF schützen
- [x] Check-in/Check-out-Links durch Formulare mit Bestätigung ersetzen
- [x] Öffentlichen Kiosk-Endpunkt vom Verwaltungsbereich trennen und auf die notwendigen Funktionen beschränken
- [x] Rate-Limits und Missbrauchsschutz für Login und Kiosk-Aktionen ergänzen

## 2. Sitzungen, Transport und Webserver

- [x] Session-Konfiguration zentral vor dem ersten Session-Start setzen
- [x] `HttpOnly` und `SameSite` für Session-Cookies konfigurieren
- [ ] `Secure` für Session-Cookies nach der HTTPS-Aktivierung im Produktivbetrieb erzwingen
- [x] Session-ID nach erfolgreichem Login erneuern
- [x] Inaktive Sitzungen mit angemessener Ablaufzeit beenden
- [ ] HTTPS auch im internen Netz aktivieren und HTTP auf HTTPS umleiten
- [x] Security-Header setzen: Content-Security-Policy, X-Content-Type-Options, Referrer-Policy und Frame-Schutz
- [x] `.env` außerhalb des Document-Roots ablegen
- [x] Falls Verschieben kurzfristig nicht möglich ist: Zugriff auf `config/`, `backup/`, `logs/`, `vendor/` und versteckte Dateien serverseitig sperren
- [x] Datenbankzugangsdaten rotieren und ausschließlich über geschützte Serverkonfiguration beziehen
- [x] Datenbankbenutzer auf die tatsächlich benötigten Rechte beschränken

## 3. Eingaben, Ausgaben und Datenintegrität

- [x] Alle Eingaben serverseitig typisieren, normalisieren und mit Längenlimits validieren
- [x] Rollen nur gegen eine feste Whitelist akzeptieren
- [x] Datumsfelder strikt als gültige Kalenderdaten prüfen und Startdatum <= Enddatum erzwingen
- [x] HTML-Ausgabe kontextbezogen escapen, besonders `data-*`-Attribute und Formularwerte
- [x] Generische Fehlermeldungen ausgeben und technische Details ausschließlich loggen
- [x] Logs auf Benutzer-ID, Aktion, Zielobjekt, Zeitpunkt und Ergebnis umstellen; keine Passwörter, Tokens oder personenbezogenen Rohdaten loggen
- [ ] Doppelten Besuchernamen durch eindeutige fachliche Identifikation und Dublettenprüfung vorbeugen
- [x] Check-in-Prüfung und INSERT atomar machen oder durch Datenbankregel/Transaktion gegen parallele Doppel-Check-ins absichern
- [x] Löschverhalten für Besucher und zugehörige Besuche fachlich festlegen und technisch korrekt umsetzen
- [x] Fehler und betroffene Datensätze bei Update/Delete auswerten, statt Erfolg pauschal zu melden
- [x] Für große Besucher- und Besuchslisten Pagination, Suche und passende Datenbankindizes ergänzen

## 4. Backup und Abhängigkeiten

- [x] Backup-Erzeugung ohne ungeschützte Shell-Interpolation umsetzen
- [x] Backups außerhalb des Webroots speichern
- [x] Backup-Dateien verschlüsseln oder durch Dateisystemrechte schützen
- [x] Aufbewahrung und Rotation der Backups definieren
- [x] Wiederherstellung der Backups praktisch testen und dokumentieren
- [x] Regelmäßigen Restore-Test dokumentieren
- [x] TCPDF auf eine behobene Version aktualisieren
- [x] `composer.json` und `composer.lock` synchronisieren
- [x] `composer audit` ohne bekannte sicherheitsrelevante Warnungen ausführen
- [x] Nicht benötigte Pakete, doppelte FPDF-Versionen und Vendor-Beispiele entfernen
- [x] Deployment-Prozess mit reproduzierbarer Dependency-Installation einrichten

## 5. Einheitliche technische Struktur

- [x] Einheitliche Routing-Konvention für alle Endpunkte festlegen
- [x] POST-Aktionen, GET-Anzeigen und Redirects konsistent trennen
- [x] Doppelte Detail-Views und Sicherungsdateien wie `models/Visitor.php.bu` entfernen
- [x] Authentifizierung, Autorisierung, CSRF, Validierung und Response-Handling zentral kapseln
- [ ] Gemeinsame Layout- und Meldungskomponenten verwenden
- [x] Zeichencodierung aller Dateien auf UTF-8 ohne beschädigte Sonderzeichen vereinheitlichen
- [x] PHP-Version und Produktionskonfiguration dokumentieren
- [x] Fehlerbehandlung so umstellen, dass `display_errors` in Produktion deaktiviert bleibt

## 6. Kiosk-Modus für den Empfang

- [x] Separate Kiosk-Oberfläche mit großer Bedienung und wenigen Eingabefeldern erstellen
- [x] Nach erfolgreicher Aktion automatisch zum Startbildschirm zurückkehren
- [x] Im Kiosk keine Besuchshistorien oder vollständigen Verwaltungsdaten anzeigen
- [x] Check-in mit vorab registriertem Termin oder Besucher-ID ermöglichen
- [x] Check-out nur für eindeutig identifizierte aktive Besuche erlauben
- [x] Kiosk-Aktionen protokollieren, ohne unnötige personenbezogene Daten auf dem Bildschirm zu zeigen
- [x] Kiosk-Zugriff technisch auf festgelegte Geräte oder Netzsegmente begrenzen

## 7. Vorab registrierte Besuche und QR-Codes

- [x] Besuchstermine mit Besucher, Gastgeber, Abteilung, Zeitraum und Besuchsgrund modellieren
- [x] Zustände `angemeldet`, `eingecheckt`, `ausgecheckt` und `abgelaufen` definieren
- [x] Zeitlich begrenzte, nicht erratbare QR-Codes erzeugen
- [x] QR-Codes serverseitig validieren und nach Nutzung bzw. Ablauf entwerten
- [ ] Manuelle Suche und Korrektur für Empfangsmitarbeiter vorsehen
- [x] Abgelaufene und nicht erschienene Termine automatisch kennzeichnen

## 8. Gastgeber und Benachrichtigungen

- [x] Gastgeber oder Abteilung einem Besuch zuordnen
- [x] Benachrichtigung beim Check-in vorbereiten
- [x] Versandfehler protokollieren und im Verwaltungsbereich sichtbar machen
- [x] Benachrichtigungskanäle konfigurierbar machen, zunächst E-Mail oder interner Hinweis
- [x] Keine sensiblen Besuchsdaten in Betreffzeilen oder ungeschützten Nachrichten verwenden

## 9. Besucher- und Besucherverwaltung

- [x] Suche nach Name, Firma und Besucher-ID anbieten
- [ ] Dubletten beim Anlegen erkennen und zur Zusammenführung vorschlagen
- [x] Historie, aktuelle Anwesenheit und geplante Besuche getrennt darstellen
- [x] Korrekturen und Löschungen mit Änderungsprotokoll versehen
- [ ] CSV-Import und kontrollierten Export nur bei konkretem Bedarf ergänzen
- [ ] Ausweis- und QR-Code-Status am Besucher anzeigen

## 10. Datenschutz

- [x] Aufbewahrungsfristen für Besucher- und Besuchsdaten fachlich festlegen
- [x] Automatische Löschung oder Anonymisierung nach Ablauf der Frist implementieren
- [x] Einzelne Datensätze löschen/anonymisieren können
- [x] Zugriffe auf Besuchshistorien und Exporte auditierbar protokollieren
- [x] Datenschutzkonzept mit Datenkategorien, Rollen, Fristen und Betroffenenrechten vorbereiten
- [ ] Datenschutzhinweise und gegebenenfalls Einwilligung im Kiosk integrieren
- [ ] Backups und Logs in die Datenschutz- und Löschkonzeption einbeziehen

## 11. Berichte und Auswertungen

- [x] Berichte nur nach Rolle und Berechtigung verfügbar machen
- [x] Filter nach Zeitraum, Besucher, Firma, Gastgeber, Standort und Status ergänzen
- [x] Anwesenheitsdauer und aktuelle Belegung berechnen
- [x] CSV- und Excel-kompatiblen Export ergänzen
- [x] PDF-Ausgabe nach Dependency-Update testen
- [x] Exportgrößen begrenzen und große Berichte paginieren oder asynchron erzeugen
- [ ] Gespeicherte Standardberichte und Monatsberichte nur bei fachlichem Bedarf ergänzen

## 12. Standorte, Drucker und Besucherausweise

- [x] Standorte, Gebäude, Eingänge und Abteilungen als eigene Stammdaten modellieren
- [x] Besuche einem Standort zuordnen und nach Standort auswerten
- [x] Drucker pro Standort konfigurieren
- [x] Besucherausweise mit Name, Gastgeber, Gültigkeitszeitraum und QR-Code erzeugen
- [x] Ausweis bei Check-out oder Ablauf deaktivieren
- [ ] Druckfehler und erneuten Druck nachvollziehbar protokollieren

## 13. Tests und Abnahme

- [x] PHP-Syntaxcheck für alle Anwendungsdateien ausführen
- [x] Dependency-Validierung und `composer audit` ausführen
- [x] Anonyme Zugriffe auf alle geschützten Seiten prüfen
- [ ] Für jede Rolle erlaubte und verbotene Aktionen testen
- [ ] CSRF-Tests für jede schreibende Aktion durchführen
- [x] GET-Aufrufe gegen schreibende Endpunkte müssen abgewiesen werden
- [ ] XSS-Testdaten in Namen, Firmen, Besuchsgründen und Filtern verwenden
- [ ] Ungültige Datumsbereiche, überlange Eingaben und ungültige IDs testen
- [ ] Parallele Check-ins desselben Besuchers testen
- [x] Backup-Erzeugung und Wiederherstellung testen
- [ ] Session-Ablauf, Session-ID-Wechsel und Cookie-Attribute prüfen
- [ ] Datenschutz-Löschung und Anonymisierung mit Testdaten prüfen
- [x] Kiosk-Ablauf auf Empfangsgeräten testen
- [x] Bericht und PDF-Ausgabe mit vorhandenen Daten testen
- [x] Fachliche Abnahme durch den Betreiber bestätigen
- [ ] Organisatorische Freigabe mit Risikoakzeptanz dokumentieren
- [ ] CSV-Export und Pagination mit großen Datenmengen testen
- [ ] Nach jedem Release einen kurzen manuellen Smoke-Test durchführen

## Definition of Done

- [ ] Keine kritischen oder hohen Sicherheitsbefunde offen
- [ ] Alle schreibenden Endpunkte sind authentifiziert, autorisiert, CSRF-geschützt und POST-only
- [ ] Keine Geheimnisse, Logs oder Backups sind öffentlich aus dem Webroot abrufbar
- [ ] Abhängigkeiten sind aktuell geprüft und reproduzierbar installierbar
- [ ] Rollen- und Kiosk-Verhalten ist dokumentiert und getestet
- [ ] Datenschutzfristen und Löschprozesse sind umgesetzt
- [ ] Die Anwendung besteht Syntax-, Sicherheits- und Integrationstests
- [ ] Betriebsdokumentation für Installation, Backup, Restore und Notfallbetrieb ist vorhanden

## 16. Quick Wins ohne HTTPS

- [x] App-Seiten mit `Cache-Control: no-store` und `Pragma: no-cache` versehen
- [x] Sensible Dateien wie Setup, SQL, Logs, Backups und `.user.ini` per Apache sperren
- [x] `robots.txt` und `security.txt` bereitstellen
- [x] Bootstrap lokal ausliefern und externe CDN-Quellen aus der CSP entfernen
- [x] Apache-Module und Serverinformationen im Setup härten
- [x] HTTP-Sicherheits-Smoke-Tests automatisieren
- [x] Kiosk-Rate-Limits nach IP, Sitzung und Gerätekennung ergänzen
- [x] Optionalen Kiosk-Gerätetoken über `X-Kiosk-Device-Token` vorbereiten
- [ ] Kiosk-Gerätetoken aktivieren (bewusst zurückgestellt)
- [ ] HTTPS, Zertifikat, Weiterleitung und HSTS produktiv umsetzen (bewusst zurückgestellt)

## 14. Schlüssel, konfigurierbare Vorlagen und Kioskdruck

- [x] Schlüsselbestand mit Standort, Status und Inventarnummer modellieren
- [x] Schlüssel beim Check-in transaktional ausgeben und beim Check-out zurücknehmen
- [x] Schlüsselvergabe als aktive Empfangsentscheidung statt als automatische Standardaktion umsetzen
- [x] Historie der Schlüsselzuordnungen mit Besucher, Ausgabe, Rückgabe und verantwortlichen Benutzern anzeigen
- [x] Schlüsselausgabe aus Index und Kiosk über eine Auswahl-Lightbox ermöglichen
- [x] Druckdatenmodell von der Darstellung trennen
- [x] HTML-, PDF/TCPDF- und Jasper-Renderer als austauschbare Vorlagenadapter anlegen
- [x] Vorlagenvariablen serverseitig whitelisten und escapen
- [x] Jasper-Renderer mit separater Serviceauthentifizierung absichern
- [x] Vorlagenverwaltung mit Versionierung und Testausgabe als Adminoberfläche ergänzen
- [x] Druckprofile für BON-, Label- und Besucherausweise verwalten
- [x] Druckjobs über CUPS/IPP oder einen lokalen Kiosk-Druckagenten ausliefern
- [x] Druckjobstatus, Fehler und Wiederholungsdruck im Adminbereich verwalten
- [x] Schlüssel-, Druck- und Vorlagenaktionen für das Auditlog vorbereiten
- [x] Auditlog als filterbare Adminansicht mit CSV-Export bereitstellen
- [x] Auditlog um Ergebnis, IP, Gerätekennung und Metadaten erweitern
- [x] Audit-Aufbewahrung auf 365 Tage in den CLI-Aufbewahrungslauf integrieren
- [x] Auditlog nach Zeitraum, Benutzer, Aktion und Ergebnis filtern

## 15. Bedienung und Kiosk-Oberfläche

- [x] Besucherverwaltung nach ID, Name, Firma und Anlagedatum sortierbar machen
- [x] Sortierung über Seitenwechsel und Suchfilter hinweg beibehalten
- [x] Kiosk-Oberfläche mit lokalem, netzunabhängigem Stylesheet ausstatten
- [x] Kiosk-Layout für Touch-Bedienung und kleine Bildschirme optimieren
- [x] Kiosk-Check-in per Besucher-ID ermöglichen; Termin-Code bleibt serverseitig vorbereitet
- [x] Aktuell eingecheckte Besucher im Kiosk mit direktem Check-out anzeigen
- [x] Kiosk-Meldungen automatisch ausblenden und Logo lokal ausliefern
