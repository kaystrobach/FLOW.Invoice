<?php

namespace KayStrobach\Invoice\View;

use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use Neos\Utility\ObjectAccess;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class AccountingRecordsView extends AbstractExcelView
{
    /**
     * @var csv
     */
    protected $excelTemplate = 'resource://KayStrobach.Invoice/Private/Templates/AccountingRecord/Index.xlsx';

    public function renderValues(Spreadsheet $sheet, int $firstRow): array
    {
        $rows = [];

        /** @var AccountingRecord $record */
        foreach ($this->variables['values'] as $record) {
            $rows[] = [
                $record->getAmount(), # formatierung mit komma
                $record->getShouldOrHave(),
                '', # WKZ Umsatz
                '', # Kurs
                '', # Basis Umsatz
                '', # WKZ Basis Umsatz
                $record->getAccount(),
                $record->getOffsetAccount(),
                '', # BU Schlüssel
                $this->date($record->getInvoice()->getDate()),
                $record->getInvoice()->getNumber()->getCombinedNumber(),
                $this->date($record->getDueDate()),
                '', # Skonto
                $record->getText()

            ];
        }
        return $rows;
    }

    protected function date(\DateTime $date = null)
    {
        if ($date === null) {
            return '';
        }
        return $date->format('dmy');
    }
}
