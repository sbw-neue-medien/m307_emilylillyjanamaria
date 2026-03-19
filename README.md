## Projektinformationen

**Team:** Maria, Jana, Lily, Emily
**Modul:** 307
**Abgabe:** 19.02.2025

---

## Anforderungen (User Stories)

| ID | Als...   | möchte ich...                                     | damit...                                 |
| -- | -------- | ------------------------------------------------- | ---------------------------------------- |
| 1  | Benutzer | Benutzer verwalten können                         | ich Zugriff und Rollen steuern kann      |
| 2  | Benutzer | Stunden eintragen können                          | ich meine Arbeitszeit dokumentieren kann |
| 3  | Benutzer | Projekte erstellen, bearbeiten und löschen können | ich meine Arbeit organisieren kann       |
| 4  | Benutzer | Kundenanfragen verwalten können                   | ich den Überblick über Kunden behalte    |

---

## Technologien

URI	Service
localhost:9080/	webroot, contents of folder = ./htdocs
localhost:9081/	adminer, a web based data manager
localhost:9306	external db connection (only needed for external access)

### Frontend

* HTML5 (semantische Tags)
* CSS3 (zentrale Stylesheet-Datei)
* JavaScript (Validierung)

### Backend

* PHP (Sessions für Login)
* PDO für sichere Datenbankabfragen

### Datenbank

* MySQL

---

## Funktionen

* Benutzerregistrierung und Login
* Projektverwaltung
* Zeiterfassung (Stunden eintragen)
* Verwaltung von Kundenanfragen
* Responsives Design

---

## Setup-Anleitung

### Voraussetzungen

* Docker
* Webbrowser (Chrome, Firefox, Edge)

## Ordnerstruktur

```
├── css/              # Styles
├── js/               # JavaScript
├── pages/         # PHP Includes (DB, Auth)
├── index.php         # Hauptseite
├── login.php
├── dashboard.php
├── logout.php
├── database.sql      # Datenbank
```

---

## Tests

### Test 1: Login mit falschen Daten

**Erwartet:** Fehlermeldung wird angezeigt
**Resultat:** ✅ funktioniert

### Test 2: Projekt erstellen

**Erwartet:** Projekt wird gespeichert und angezeigt
**Resultat:** ✅ funktioniert

### Test 3: Stunden eintragen

**Erwartet:** Stunden werden gespeichert
**Resultat:** ✅ funktioniert

---

## Design

Das Design ist modern und übersichtlich gestaltet.
Fokus liegt auf einfacher Bedienung und klarer Struktur.

---

## Hinweise

Dieses Projekt wurde im Rahmen des Moduls 307 erstellt.
