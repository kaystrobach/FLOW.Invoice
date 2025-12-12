<?php

namespace KayStrobach\Invoice\View;

use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\Domain\Model\SettlementDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SettlementDatesExcelView extends AbstractExcelView
{
    /**
     * @var csv
     */
    protected $excelTemplate = 'resource://KayStrobach.Invoice/Private/Templates/SettlementDate/Index.xlsx';

    public function renderValues(Spreadsheet $excelFileObject, int $firstRow): array
    {
        $rows = [];

        /** @var SettlementDate $record
         */
        foreach ($this->variables['objects'] as $record) {
            $rows[] = [
                $record->getInvoice()->getNumber()->getCombinedNumber()(),
                $record->getInvoice()->getCustomer()->getDeptorNumber(),
                $record->getDueDate()->format('d.m.Y'),
                $record->isPaid() ? 'ja' : 'nein',
                $record->isPaymentDelayed() ? 'ja' : 'nein',
                $record->getComment(),
                $record->getAmount()

            ];
        }
        return $rows;
    }
}
