# Beitrittserklärung – WordPress-Plugin

DSGVO-konformes WordPress-Plugin zur Erfassung von Vereins-Beitrittserklärungen mit PDF-Versand, Backend-Archiv und Gutenberg-/DIVI-5-Integration.

## Funktionen

- Formular mit geclusterten Feldern (persönliche Daten, Anschrift, Kontakt, Mitgliedschaft, SEPA, Datenschutz)
- Einzelne Felder im Backend ausblendbar und als Pflichtfeld konfigurierbar
- PDF-Generierung und Versand an Verein + Kopie an Antragsteller via `wp_mail()` (kompatibel mit **WP Mail SMTP**)
- Schreibgeschütztes Backend-Archiv aller Einreichungen inkl. IP-Adresse
- Automatische Löschung nach **12 Monaten** (DSGVO)
- Cloudflare **Turnstile** (kostenfrei, werbefrei, DSGVO-freundlich) + Honeypot
- Gutenberg-Block und DIVI-5-Modul
- Shortcode: `[beitrittserklaerung]`

## Installation

1. Plugin-Verzeichnis `wp-beitrittserklaerung` nach `wp-content/plugins/` kopieren
2. Plugin im WordPress-Backend aktivieren
3. Unter **Beitrittserklärung → Einstellungen** konfigurieren:
   - Empfänger-E-Mail
   - Datenschutzseite
   - Turnstile-Schlüssel (Cloudflare Dashboard)
   - Vereinsname für SEPA-Mandatstext (`{verein}`)

## Einbindung

### Gutenberg
Block **„Beitrittserklärung“** in der Kategorie Widgets einfügen.

### DIVI 5
Modul **„Beitrittserklärung“** im Divi Builder (ab Divi 5.0).

### Shortcode
```
[beitrittserklaerung]
```

## Systemvoraussetzungen

- WordPress 6.4+
- PHP 8.1+
- WP Mail SMTP (empfohlen)
- Divi 5+ (optional, für DIVI-Modul)

## Datenschutz

- Personenbezogene Daten werden nach 12 Monaten automatisch gelöscht
- IP-Adresse wird zum Nachweis der Einreichung gespeichert (keine Geolokalisierung)
- Explizite Einwilligung über konfigurierbare Checkbox mit Link zur Datenschutzseite

## Lizenz

GPL-2.0-or-later
