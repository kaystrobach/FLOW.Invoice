<?php

namespace KayStrobach\Invoice\View;

use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use Neos\Utility\ObjectAccess;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;


class AccountingRecordsCsvView extends AccountingRecordsView
{
    /**
     * @var string
     */
    protected $excelTemplate = 'resource://KayStrobach.Invoice/Private/Templates/AccountingRecord/Index.xlsx';

    public function renderValues(Spreadsheet $excelFileObject, int $firstRow): array
    {
        $this->setOption('fileExtension', 'csv');
        $this->setOption('writer', 'CSV');

        return parent::renderValues($excelFileObject, $firstRow);
    }

    /**
     * @param IWriter $writer
     * @return IWriter
     */
    protected function configureWriter(IWriter $writer): IWriter
    {
        if ($writer instanceof Csv) {
            $writer->setDelimiter(';');
        }
    }
}
