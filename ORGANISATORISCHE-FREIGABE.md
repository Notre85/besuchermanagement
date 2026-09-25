# Organisatorische Freigabe des Besuchermanagements

## 1. Freigabegegenstand

| Feld | Eintrag |
|---|---|
| Anwendung | Besuchermanagement |
| Verzeichnis/Instanz | `/var/www/besuchermanagement` |
| Betriebsumgebung | Eigene interne VM |
| Freigabestand | ______________________________ |
| Datum der Freigabe | ______________________________ |
| Verantwortliche Stelle | ______________________________ |
| Fachlich verantwortliche Person | ______________________________ |
| Technisch verantwortliche Person | ______________________________ |
| Vertretung | ______________________________ |

## 2. Zweck und Geltungsbereich

Die Anwendung verwaltet Besucher, geplante Besuche, Check-ins, Check-outs,
Schlüsselzuordnungen, Besucherausweise, Druckaufträge und Auditdaten. Der
Kiosk ist auf den Besucher-Check-in und die Anzeige aktiver Besucher begrenzt.
Schlüssel werden ausschließlich durch angemeldete Beschäftigte ausgegeben.

Die Freigabe gilt nur für den Betrieb im kontrollierten internen Netz. Eine
Veröffentlichung über Internet, Gäste-WLAN, nicht kontrollierte VPN-Zugänge
oder weitere Standorte ist nicht Bestandteil dieser Freigabe.

## 3. Sicherheits- und Betriebsnachweise

Die unterzeichnende Stelle bestätigt, dass folgende Dokumente gelesen und als
Betriebsgrundlage akzeptiert wurden:

- [Sicherheits- und Betriebsdokumentation](SICHERHEITSBETRIEBS-DOKUMENTATION.md)
- [Risikoanalyse internes HTTPS](RISIKOANALYSE-INTERNES-HTTPS.md)
- [BSI-APP-Audit](BSI-APP-AUDIT.md)
- [Sicherheits- und Weiterentwicklungs-Checkliste](SICHERHEITS-UND-WEITERENTWICKLUNGS-CHECKLISTE.md)

## 4. Freigabeentscheidungen

Bitte jeweils ankreuzen und gegebenenfalls eine Auflage ergänzen:

| Entscheidung | Ja | Nein | Auflage/Begründung |
|---|:---:|:---:|---|
| Der beschriebene Zweck und Geltungsbereich werden freigegeben. | [ ] | [ ] | ______________________________ |
| Die Rollen und Berechtigungen werden freigegeben. | [ ] | [ ] | ______________________________ |
| Der Kiosk darf im freigegebenen internen Netz anonymes Besucher-Check-in anbieten. | [ ] | [ ] | ______________________________ |
| Die dokumentierten Aufbewahrungs- und Löschfristen werden freigegeben. | [ ] | [ ] | ______________________________ |
| Backup, Restore und der manuelle Notfallbetrieb sind geregelt. | [ ] | [ ] | ______________________________ |
| Die fachliche Abnahme gilt als erfolgt. | [x] | [ ] | Betreiberbestätigung vom 25.09.2026 |
| Das Restrisiko aus dem Betrieb ohne internes HTTPS wird akzeptiert. | [ ] | [ ] | ______________________________ |
| Die offenen Produktionsmaßnahmen werden nachverfolgt. | [ ] | [ ] | Verantwortlich: __________ Termin: __________ |

## 5. Verbindliche Restauflagen

Folgende Punkte bleiben nach der Freigabe als Auflagen bestehen, sofern sie
nicht vor Freigabe erledigt werden:

- Apache-Laufzeitkonfiguration auf der VM härten: `ServerTokens Prod`,
  `ServerSignature Off`, `Options -Indexes` und unnötige Module prüfen.
- Quellcode und Deployment-Dateien gegen Schreibzugriff durch `www-data`
  absichern; nur Laufzeitverzeichnisse wie Logs dürfen beschreibbar sein.
- Entscheiden, ob der Kiosk-Gerätetoken aktiviert wird, und die Aktivierung
  anschließend testen.
- Datenschutzkonzept für Besucherdaten, Backups und Logs organisatorisch
  bestätigen.
- Penetrationstest beziehungsweise Sicherheitsrevision terminieren.

## 6. Erklärung

Mit der Unterschrift wird bestätigt, dass die Anwendung im oben beschriebenen
Umfang betrieben werden darf, die genannten Restrisiken bekannt sind und die
Auflagen nachverfolgt werden. Die Freigabe ersetzt keine technische Prüfung
und gilt nicht automatisch für spätere Funktions- oder Netzänderungen.

| Funktion | Name | Datum | Unterschrift |
|---|---|---|---|
| Verantwortliche Stelle | __________________ | __________ | __________________ |
| Fachliche Verantwortung | __________________ | __________ | __________________ |
| IT-Administration | __________________ | __________ | __________________ |
