### KayStrobach.Invoice

Ein Neos Flow Paket zur Erstellung und Verwaltung von Rechnungen im KIS/Flow‑Umfeld mit Unterstützung für E‑Rechnung (XRechnung/ZUGFeRD) auf Basis der PHP‑Bibliothek `horstoeko/zugferd`.

#### Funktionen
- Rechnungserstellung im Systemkontext von Neos Flow
- Generierung strukturierter Rechnungsdaten (XML) gemäß EN 16931 Profilen
- Unterstützung gängiger E‑Rechnungsformate via `horstoeko/zugferd` (ZUGFeRD 2.x, XRechnung 2.x/3.x – abhängig von der verwendeten Bibliotheksversion)
- Vorbereitung für DATEV‑Export (siehe `Configuration/Settings.yaml` → `KayStrobach.Invoice.Datev`)

#### Voraussetzungen
- PHP 8.2
- Neos Flow ^8.3
- Abhängigkeiten (Auszug):
  - `horstoeko/zugferd`
  - `moneyphp/money`

Die vollständigen Abhängigkeiten sind in `composer.json` hinterlegt.

#### Installation
- Das Paket ist in diesem Projekt bereits eingebunden. In einem separaten Projekt via Composer:
  - `composer require kaystrobach/invoice`

#### Konfiguration
Anpassbare Voreinstellungen befinden sich in `Configuration/Settings.yaml`.

Beispielauszug und Bedeutung:

```
KayStrobach:
  Invoice:
    Default:
      Invoice:
        numberPrefix: R            # Präfix für Rechnungsnummern
        receiverBic:  BICMISSING   # BIC des Rechnungsempfängers (eigene Bank)
        receiverName: Company      # Kontoinhaber
        receiverIban: DE 12 34 ... # IBAN
    Datev:
      Mandant: 123456             # DATEV Mandant
      Beraternummer: 1234567       # DATEV Beraternummer
      WirtschaftsjahrBeginn: '%year%0101' # Beginn Wirtschaftsjahr (yyyyMMdd)
      Sachkontennummernlaenge: 4   # Länge der Sachkontennummern
      ExportiertVon: KayStrobach   # Metadaten Export
      Herkunft: RE                 # Belegherkunft
      Bezeichnung: Rechnungen      # Exportbezeichnung
      Festschreibung: 1            # Festschreibung (1/0)
      WKZ: EUR                     # Währung
```

Hinweise:
- Ersetzen Sie Platzhalter (IBAN/BIC/Name) durch Ihre echten Daten.
- `numberPrefix` steuert das Präfix neuer Rechnungsnummern, z. B. `R2025-…`.
- Die DATEV‑Parameter beeinflussen ausschließlich den Export und nicht die XML‑Erstellung der E‑Rechnung.

#### E‑Rechnung (XRechnung/ZUGFeRD)
Dieses Paket nutzt die Bibliothek `horstoeko/zugferd`, um die strukturierten Rechnungsdaten (XML) zu erzeugen. Die Auswahl des Profils (z. B. EN 16931, BASIC/COMFORT/EXTENDED) sowie Formatdetails hängen von Ihrer fachlichen Anforderung und den Vorgaben Ihrer Empfänger ab.

Wichtige Praxispunkte (inspiriert von „Umstellung auf E‑Rechnung mit PHP“ von brumble.dev – siehe Link unten):
- Vollständigkeit: Pflichtangaben gemäß EN 16931 (u. a. Rechnungsnummer, Datum, Verkäufer/Käufer, Steuern, Summen) müssen vorhanden und konsistent sein.
- Steuern: Weisen Sie korrekte Steuerschlüssel, Steuersätze und Steuerbeträge je Position/Aufschlüsselung aus.
- Zahlungsinformationen: IBAN/BIC, Zahlungsziel(e) und mögliche Skontodaten strukturiert hinterlegen.
- Referenzen: Geben Sie – falls gefordert – Bestell-/Leistungs‑Referenzen (z. B. `OrderReference`, `BuyerReference`) an.
- Profilwahl: Stimmen Sie das Profil (z. B. XRechnung/EN 16931) mit den Anforderungen Ihrer Rechnungsempfänger ab.

Weitere Hintergründe und ein Einstieg in die Arbeit mit `horstoeko/zugferd` finden Sie im Artikel:
- https://brumble.dev/de/blog/umstellung-auf-e-rechnung-mit-php

Und in der Bibliotheksdokumentation:
- https://github.com/horstoeko/zugferd

#### Verwendung (Überblick)
Die eigentliche Objekterzeugung und das Mapping auf das XML erfolgt über interne Services dieses Pakets, die wiederum `horstoeko/zugferd` verwenden. Typischer Ablauf:
1. Erstellen/Ermitteln der Rechnungsdaten (Kunde, Positionen, Beträge, Steuern, Zahlungsziel, Referenzen).
2. Übergabe an den internen Generator/Mapper.
3. Erzeugung der strukturierten Rechnung (XML) im gewünschten Profil.
4. Optional: Einbettung der XML in ein PDF (ZUGFeRD/Factur‑X) oder Versand/Upload im jeweiligen Kanal.

Hinweise für Integrationen:
- Beträge sollten als `Money`‑Objekte (`moneyphp/money`) geführt werden, um Rundungsfehler zu vermeiden.
- Achten Sie auf die in Ihrem Anwendungsfall geforderte Profil‑ und Versionskompatibilität (z. B. XRechnung Version des Portals Ihrer Behörde/Partner).

#### Entwicklung & Tests
- Der Quellcode folgt dem üblichen Neos Flow‑Package Layout.
- Migrationen befinden sich unter `Migrations/`.
- Passen Sie Einstellungen und Services projektspezifisch an; achten Sie auf saubere Domänenmodelle und eindeutige Rechnungsnummern.

#### Support & Lizenz
- Maintainer: Kay Strobach / 4viewture
- Lizenz: entsprechend Projektlizenz (siehe Wurzel‑`composer.json` bzw. Paketlizenz)

#### Testsystem der Länder:

* https://test.xrechnung-bdr.de/edi/auth/login
