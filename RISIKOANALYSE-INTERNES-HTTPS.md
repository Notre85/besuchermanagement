# Risikoanalyse: Betrieb ohne internes HTTPS

Stand: **25.09.2026**
Geltungsbereich: Besuchermanagement auf einer eigenen VM im internen Netz

## Entscheidung

Der Betrieb ohne HTTPS wird für den aktuellen internen Einsatz vorläufig
akzeptiert. Die Anwendung ist nicht aus dem Internet veröffentlicht. Die
Entscheidung gilt nur, solange die unten beschriebenen Randbedingungen
eingehalten und regelmäßig geprüft werden.

HTTPS bleibt eine spätere Maßnahme, sobald sich Netzgrenzen, WLAN-Nutzung,
Standorte, Fernzugriffe oder die Schutzbedarfsbewertung ändern.

## Schutzbedarf

Die Anwendung verarbeitet personenbezogene Besucherdaten, Anwesenheitszeiten,
Gastgeber, Besuchsgründe sowie Schlüsselzuordnungen. Daraus ergibt sich:

| Schutzziel | Bewertung | Begründung |
|---|---|---|
| Vertraulichkeit | mittel | Besuchs- und Anwesenheitsdaten dürfen nicht beliebig im Netz mitgelesen werden. |
| Integrität | hoch | Manipulierte Check-ins, Check-outs oder Schlüsselzuordnungen können den Betrieb und die Sicherheit am Standort beeinträchtigen. |
| Verfügbarkeit | mittel | Ein Ausfall ist störend, kann aber durch den dokumentierten manuellen Notfallbetrieb überbrückt werden. |

## Betrachtete Bedrohungen

| Bedrohung | Eintrittswahrscheinlichkeit | Auswirkung | Risiko ohne HTTPS |
|---|---:|---:|---:|
| Mitschneiden von Besucherdaten im internen Netz | niedrig bis mittel | mittel | mittel |
| Manipulation einer HTTP-Anfrage durch einen bereits im Netz befindlichen Angreifer | niedrig bis mittel | hoch | hoch |
| Missbrauch eines frei erreichbaren Kiosk-Endpunkts | niedrig | mittel | mittel |
| Angreifer aus dem Internet | niedrig | hoch | niedrig bis mittel, sofern keine Veröffentlichung/Weiterleitung besteht |
| Unbefugter Zugriff über kompromittiertes WLAN oder Endgerät | mittel | hoch | hoch |

## Vorhandene kompensierende Maßnahmen

- Die Anwendung ist für eine interne VM vorgesehen und nicht als öffentliches
  Internetangebot freigegeben.
- Der Kiosk ist auf freigegebene Netzbereiche begrenzt.
- Verwaltungsfunktionen benötigen Anmeldung und Rollenberechtigung.
- Schreibaktionen sind POST-only und CSRF-geschützt.
- Sitzungen verwenden `HttpOnly` und `SameSite=Lax`; die Secure-Option wird mit
  der späteren HTTPS-Aktivierung erzwungen.
- Kiosk-Aktionen haben Rate-Limits nach IP, Sitzung und Gerätekennung.
- Security-Header, Cache-Control, Dateisperren und Auditlog sind aktiv.
- Backups liegen außerhalb des Webroots und werden mit restriktiven Rechten
  gespeichert.
- Der Empfang besitzt einen dokumentierten manuellen Notfallbetrieb.

## Randbedingungen für die Risikoakzeptanz

Die Entscheidung darf nur gelten, wenn alle folgenden Punkte eingehalten
werden:

1. Port 80 ist nur aus den freigegebenen internen Netzsegmenten erreichbar.
2. Es gibt keine Portweiterleitung, keinen Reverse Proxy aus dem Internet und
   keinen unkontrollierten Fernzugriff auf die Anwendung.
3. Kiosk und Verwaltungsarbeitsplätze verwenden verwaltete Endgeräte.
4. Das interne WLAN ist angemessen geschützt und organisatorisch kontrolliert.
5. Apache, PHP, MariaDB und Betriebssystem werden regelmäßig aktualisiert.
6. Zugriffe, Fehlversuche und Schlüsselaktionen werden ausgewertet.
7. Die Risikoentscheidung wird bei Netzwerk- oder Funktionsänderungen neu
   bewertet, mindestens jedoch jährlich.

## Restrisiko und Entscheidungsträger

Das verbleibende Hauptrisiko ist die fehlende Vertraulichkeit und
Manipulationssicherheit auf dem Transportweg innerhalb des Netzes. Dieses
Restrisiko wird für den beschriebenen internen Einsatz akzeptiert, sofern die
Randbedingungen eingehalten werden. Die Freigabe muss durch die für den
Anwendungsbetrieb verantwortliche Stelle erfolgen; die IT-Administration
bestätigt die Netz- und Patchbedingungen. Die Empfangsleitung bestätigt
zusätzlich, dass der Notfallbetrieb praktisch durchführbar ist.

## Auslöser für eine Neubewertung

Eine erneute Bewertung ist erforderlich, wenn die Anwendung über weitere
Standorte, Gäste-WLAN, VPN, Internet, mobile Geräte oder einen nicht vollständig
kontrollierten Proxy erreichbar wird. Gleiches gilt, wenn besonders schützens-
werte Daten ergänzt oder Schlüssel- und Zutrittsfunktionen erweitert werden.
