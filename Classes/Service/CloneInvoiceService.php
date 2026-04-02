<?php

namespace KayStrobach\Invoice\Service;

use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\Invoice\TaxRecord;
use KayStrobach\Invoice\Domain\Model\InvoiceItem;

class CloneInvoiceService
{
    public function clone(Invoice $invoice)
    {
        $newInvoice = $invoice->makePartialClone();
        $newInvoice->setChangeable(true);
        $newInvoice = $this->cloneSubRecords($invoice, $newInvoice);
        return $newInvoice;
    }

    protected function cloneSubRecords(Invoice $invoice, Invoice $newInvoice): Invoice
    {
        /** @var InvoiceItem $invoiceItem */
        foreach ($invoice->getInvoiceItems() as $invoiceItem) {
            $newInvoiceItem = $invoiceItem->makePartialClone();
            $newInvoiceItem->getSinglePrice()->setValue($invoiceItem->getSinglePrice()->getValue());
            $newInvoiceItem->setInvoice($newInvoice);
            $newInvoice->getInvoiceItems()->add($newInvoiceItem);
        }

        $newInvoice->updateTaxRecords();
        $newInvoice->calculateTotal();
        $invoice->getAccountingRecords()->clear();

        return $newInvoice;
    }
}
