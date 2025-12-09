<?php

namespace KayStrobach\Invoice\View;

use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Annotations as Flow;


class AccountingRecordsDatevView extends \Neos\Flow\Mvc\View\AbstractView
{
    /**
     * define allowed view options
     * @var array
     */
    protected $supportedOptions = array(
        'templateRootPaths' => array(null, 'Path(s) to the template root. If NULL, then $this->options["templateRootPathPattern"] will be used to determine the path', 'array'),
        'writer' => array('Excel2007', 'Defines which writer should be used', 'string'),
        'fileExtension' => array('xlsx', 'file extension for download', 'string'),
        'startDate' =>  array(null, 'startDate of export', \DateTime::class),
        'endDate' =>  array(null, 'endDate of export', \DateTime::class),
    );

    /**
     * @var int
     */
    protected $firstRow = 2;

    /**
     * @var string
     */
    protected $excelTemplate = 'resource://KayStrobach.Invoice/Private/Views/DatevView.xlsx';

    /**
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @var string
     */
    protected $pathSegment = 'export-';

    /**
     * @Flow\InjectConfiguration(path="Datev")
     * @var array
     */
    protected $configuration;

    public function renderValues()
    {
        $this->setOption('fileExtension', 'csv');
        $this->setOption('writer', 'CSV');

        $buffer = 'Umsatz (ohne Soll/Haben-Kz);Soll/Haben-Kennzeichen;WKZ Umsatz;Kurs;Basis-Umsatz;WKZ Basis-Umsatz;Konto;Gegenkonto (ohne BU-Schlüssel);BU-Schlüssel;Belegdatum;Belegfeld 1;Belegfeld 2;Skonto;Buchungstext;Postensperren;Diverse Adressnummer;Geschäftspartnerbank;Sachverhalt;Zinssperre;Beleglink;Beleginfo - Art 1;Beleginfo - Inhalt 1;Beleginfo - Art 2;Beleginfo - Inhalt 2;Beleginfo - Art 3;Beleginfo - Inhalt 3;Beleginfo - Art 4;Beleginfo - Inhalt 4;Beleginfo - Art 5;Beleginfo - Inhalt 5;Beleginfo - Art 6;Beleginfo - Inhalt 6;Beleginfo - Art 7;Beleginfo - Inhalt 7;Beleginfo - Art 8;Beleginfo - Inhalt 8;KOST1 - Kostenstelle;KOST1 - Kostenstelle;Kost-Menge;EU-Land u. UStID;EU-Steuersatz;Abw. Versteuerungsart;Sachverhalt L+L;Funktionsergänzung L+L;BU 49 Hauptfunktionstyp;BU 49 Hauptfunktionsnummer;BU 49 Funktionsergänzung;Zusatzinformation - Art 1;Zusatzinformation- Inhalt 1;Zusatzinformation - Art 2;Zusatzinformation- Inhalt 2;Zusatzinformation - Art 3;Zusatzinformation- Inhalt 3;Zusatzinformation - Art 4;Zusatzinformation- Inhalt 4;Zusatzinformation - Art 5;Zusatzinformation- Inhalt 5;Zusatzinformation - Art 6;Zusatzinformation- Inhalt 6;Zusatzinformation - Art 7;Zusatzinformation- Inhalt 7;Zusatzinformation - Art 8;Zusatzinformation- Inhalt 8;Zusatzinformation - Art 9;Zusatzinformation- Inhalt 9;Zusatzinformation - Art 10;Zusatzinformation- Inhalt 10;Zusatzinformation - Art 11;Zusatzinformation- Inhalt 11;Zusatzinformation - Art 12;Zusatzinformation- Inhalt 12;Zusatzinformation - Art 13;Zusatzinformation- Inhalt 13;Zusatzinformation - Art 14;Zusatzinformation- Inhalt 14;Zusatzinformation - Art 15;Zusatzinformation- Inhalt 15;Zusatzinformation - Art 16;Zusatzinformation- Inhalt 16;Zusatzinformation - Art 17;Zusatzinformation- Inhalt 17;Zusatzinformation - Art 18;Zusatzinformation- Inhalt 18;Zusatzinformation - Art 19;Zusatzinformation- Inhalt 19;Zusatzinformation - Art 20;Zusatzinformation- Inhalt 20;Stück;Gewicht;Zahlweise;Forderungsart;Veranlagungsjahr;Zugeordnete Fälligkeit;Skontotyp;Auftragsnummer;Buchungstyp;Ust-Schlüssel (Anzahlungen);EU-Land (Anzahlungen);Sachverhalt L+L (Anzahlungen);EU-Steuersatz (Anzahlungen);Erlöskonto (Anzahlungen);Herkunft-Kz;Leerfeld;KOST-Datum;Mandatsreferenz;Skontosperre;Gesellschaftername;Beteiligtennummer;Identifikationsnummer;Zeichnernummer;Postensperre bis;Bezeichnung SoBil-Sachverhalt;Kennzeichen SoBil-Buchung;Festschreibung;Leistungsdatum;Datum Zuord.Steuerperiode';

        /** @var AccountingRecord $record */
        foreach ($this->variables['values'] as $record) {
            $invoiceDate = $record->getInvoice()->getDate();
            if ($this->startDate === null || $this->startDate > $invoiceDate) {
                $this->startDate = $invoiceDate;
            }
            if ($this->endDate === null || $this->endDate < $invoiceDate) {
                $this->endDate = $invoiceDate;
            }
            $buffer .= "\r\n" . $this->arrayToCsvLine(
                [
                    // Umsatz (ohne Soll/ Haben- Kennzei- chen)
                    number_format(
                        $record->getAmount(),
                        2,
                        ',',
                        ''
                    ), # formatierung mit komma
                    // Soll / Haben-Kennzeichen
                    $record->getShouldOrHave(),
                    // WKZ Umsatz
                    '',
                    // Kurs
                    '',
                    // Basis- umsatz
                    '',
                    // WKZ Basis- umsatz
                    '',
                    // Konto
                    $record->getAccount(),
                    // Gegen- konto (ohne BU-Schlüssel)
                    $record->getOffsetAccount(),
                    // BU-Schlüssel
                    '', # BU Schlüssel
                    // Belegdatum
                    $this->date($record->getInvoice()->getDate()),
                    // Belegfeld 1
                    $record->getBelegfeld1(),
                    // Belegfeld 2
                    $record->getBelegfeld2(),
                    // Skonto
                    '',
                    // Buchungstext
                    $record->getText(),
                    // Postensperre,
                    0,
                    //Diverse Adressnummer
                    '',
                    // Geschäftspartnerbank
                    '',
                    // Sachverhalt
                    '',
                    // Zinssperre,
                    '',
                    // Beleglink
                    '',
                    // Beleginfo – Art 1
                    '',
                    // Beleginfo – Inhalt 1
                    '',
                    // Beleginfo – Art 2
                    '',
                    // Beleginfo – Inhalt 2
                    '',
                    // Beleginfo – Art 3
                    '',
                    // Beleginfo – Inhalt 3
                    '',
                    // Beleginfo – Art 4
                    '',
                    // Beleginfo – Inhalt 4
                    '',
                    // Beleginfo – Art 5
                    '',
                    // Beleginfo – Inhalt 5
                    '',
                    // Beleginfo – Art 6
                    '',
                    // Beleginfo – Inhalt 6
                    '',
                    // Beleginfo – Art 7
                    '',
                    // Beleginfo – Inhalt 7
                    '',
                    // Beleginfo – Art 8
                    '',
                    // Beleginfo – Inhalt 8
                    '',
                    // KOST1 – Kosten- stelle
                    '',
                    // KOST2 – Kosten- stelle
                    '',
                    // KOST- Menge
                    '',
                    // EU-Mit- gliedstaat u. USt- IdNr.
                    '',
                    // EU-Steu- ersatz
                    '',
                    // Abw. Versteuerungsart
                    '',
                    // Sachverhalt L+L
                    '',
                    // Funktionsergänzung L+L
                    '',
                    // BU 49 Hauptfunktionstyp
                    '',
                    // BU 49 Hauptfunktionsnummer
                    '',
                    // BU 49 Funktionsergänzung
                    '',
                    // Zusatzinformation – Art 1
                    '',
                    // Zusatzinformation – Inhalt 1
                    '',
                    // Zusatzinformation – Art 2
                    '',
                    // Zusatzinformation – Inhalt 2
                    '',
                    // Zusatzinformation – Art 3
                    '',
                    // Zusatzinformation – Inhalt 3
                    '',
                    // Zusatzinformation – Art 4
                    '',
                    // Zusatzinformation – Inhalt 4
                    '',
                    // Zusatzinformation – Art 5
                    '',
                    // Zusatzinformation – Inhalt 5
                    '',
                    // Zusatzinformation – Art 6
                    '',
                    // Zusatzinformation – Inhalt 6
                    '',
                    // Zusatzinformation – Art 7
                    '',
                    // Zusatzinformation – Inhalt 7
                    '',
                    // Zusatzinformation – Art 8
                    '',
                    // Zusatzinformation – Inhalt 8
                    '',
                    // Zusatzinformation – Art 9
                    '',
                    // Zusatzinformation – Inhalt 9
                    '',
                    // Zusatzinformation – Art 10
                    '',
                    // Zusatzinformation – Inhalt 10
                    '',
                    // Zusatzinformation – Art 11
                    '',
                    // Zusatzinformation – Inhalt 11
                    '',
                    // Zusatzinformation – Art 12
                    '',
                    // Zusatzinformation – Inhalt 12
                    '',
                    // Zusatzinformation – Art 13
                    '',
                    // Zusatzinformation – Inhalt 13
                    '',
                    // Zusatzinformation – Art 14
                    '',
                    // Zusatzinformation – Inhalt 14
                    '',
                    // Zusatzinformation – Art 15
                    '',
                    // Zusatzinformation – Inhalt 15
                    '',
                    // Zusatzinformation – Art 16
                    '',
                    // Zusatzinformation – Inhalt 16
                    '',
                    // Zusatzinformation – Art 17
                    '',
                    // Zusatzinformation – Inhalt 17
                    '',
                    // Zusatzinformation – Art 18
                    '',
                    // Zusatzinformation – Inhalt 18
                    '',
                    // Zusatzinformation – Art 19
                    '',
                    // Zusatzinformation – Inhalt 19
                    '',
                    // Zusatzinformation – Art 20
                    '',
                    // Zusatzinformation – Inhalt 20
                    '',
                    // Stück
                    '',
                    // Gewicht
                    '',
                    // Zahlweise
                    '',
                    // Forderungsart
                    '',
                    // Veranlagungsjahr
                    '',
                    // Zugeordnete Fälligkeit
                    '',
                    // Skontotyp
                    '',
                    // Auftragsnummer
                    '',
                    // Buchungstyp
                    '',
                    // USt-Schlüssel (Anzahlungen)
                    '',
                    // EU-Mitgliedstaat (Anzahlungen)
                    '',
                    // Sachverhalt L+L (Anzahlungen)
                    '',
                    // EU-Steuersatz (Anzahlungen)
                    '',
                    // Erlöskonto (Anzahlungen)
                    '',
                    // Herkunft-Kz
                    '',
                    // Leerfeld
                    '',
                    // KOST-Datum
                    '',
                    // SEPA-Mandatsreferenz
                    '',
                    // Skontosperre
                    '',
                    // Gesellschaftername
                    '',
                    // Beteiligtennummer
                    '',
                    // Identifikationsnummer
                    '',
                    // Zeichnernummer
                    '',
                    // Postensperre bis
                    '',
                    // Bezeichnung SoBil-Sachverhalt
                    '',
                    // Kennzeichen SoBilBuchung
                    '',
                    // Festschreibung
                    1,
                    // Leistungsdatum
                    '',
                    // Datum Zuord. Steuerperiode
                    '',
                    // Fälligkeit
                    '',
                    // Generalumkehr
                    '',
                    // Steuersatz
                    '',
                    // Land
                    ''
                ],
                ';',
                '"',
                true,
                false
            );
        }
        return $buffer;
    }

    /**
     * Renders the view
     *
     * @throws \Exception
     * @return string The rendered view
     * @api
     */
    public function render()
    {
        // @todo check wether we can use response here to set the headers

        header('Content-type: application/ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->pathSegment . '.' . $this->getOption('fileExtension') . '"');
        header('Cache-Control: max-age=0');

        $buffer =
            $this->createDatevHeader($this->getOption('startDate'), $this->getOption('endDate')) .
            $this->renderValues();

        return $buffer;
    }

    protected function createDatevHeader(\DateTime $dateStart = null, \DateTime $dateEnd = null)
    {
        $now = new \DateTime('now');
        if ($dateStart === null) {
            $dateStart = clone $now;
        }
        if ($dateEnd === null) {
            $dateEnd = clone $now;
        }
        $wirtschaftsJahr = str_replace(
            '%year%',
            $dateStart->format('Y'),
            $this->configuration['WirtschaftsjahrBeginn'] ?? '%year%0101'
        );

        $data = [
            // DATEV-Format-KZ
            'EXTF',
            // Versionsnummer
            '700',
            // Datenkategorie 21 = Buchungsstapel
            '21',
            // Formatname
            'Buchungsstapel',
            // Formatversion
            '9',
            // Erzeugt am JJJJMMTTHHMMSS+milliseconds
            $now->format('Ymdhisv'),
            // Importiert -> muss leer sein
            '',
            // Herkunft
            $this->configuration['Herkunft'] ?? '',
            // Exportiert von
            $this->configuration['4viewture'] ?? '',
            // Importiert von --> leer
            '',
            // Berater
            $this->configuration['Beraternummer'] ?? '',
            // Mandant
            $this->configuration['Mandant'] ?? '',
            // WJ-Beginn (JJJJMMTT)
            $wirtschaftsJahr, //??
            // Sachkontennummernlänge
            $this->configuration['Sachkontennummernlaenge'] ?? '4',
            // Datum von JJJJMMTT
            $dateStart->format('Ymd'),
            // Datum bis JJJJMMTT
            $dateEnd->format('Ymd'),
            // Bezeichnung
            $this->configuration['Bezeichnung'] ?? 'Rechnungen',
            // Diktatkürzel
            '',
            // Buchungstyp
            '1',
            //Rechnungslegungszweck
            0,
            // Festschreibung
            $this->configuration['Festschreibung'] ?? '0',
            // Währung
            $this->configuration['WKZ'] ?? 'EUR',
        ];

        $csvString = '';

        foreach ($data as $key => $value) {
            if ($key !== 0) {
                $csvString .= ';';
            }
            $csvString .= '"' . $value . '"';
        }
        if (count($data) < 31) {
            for ($i = count($data); $i < 31; $i++) {
                $csvString .= ';';
            }
        }
        return $csvString. "\r\n";
    }

    protected function date(\DateTime $date = null)
    {
        if ($date === null) {
            return '';
        }
        return $date->format('dmy');
    }

    /**
     * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
     * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
     * @param array $fields
     * @param string $delimiter
     * @param string $enclosure
     * @param bool $encloseAll
     * @param bool $nullToMysqlNull
     * @param array $forceText
     * @return string
     */
    protected function arrayToCsvLine(array $fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false): string
    {
        $output = [];
        foreach ( $fields as $key => $field ) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }
            $field = utf8_decode($field);
            // Enclose fields containing $delimiter, $enclosure or whitespace
            if ($field === '' || $encloseAll || preg_match( '/(?:${delimiter_esc}|${enclosure_esc}|[a-zA-Z])/s', $field ) ) {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            }
            else {
                $output[] = $field;
            }
        }

        return implode( $delimiter, $output );
    }
}
