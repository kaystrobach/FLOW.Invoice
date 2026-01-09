<?php

namespace KayStrobach\Invoice\Service;

use KayStrobach\Invoice\Domain\Factory\InvoiceFactory;
use Neos\Flow\Annotations as Flow;

use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\Invoice\Embeddable\MoneyEmbeddable;
use KayStrobach\Invoice\Domain\Model\Invoice\TaxRecord;
use KayStrobach\Invoice\Domain\Model\InvoiceItem;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use Neos\Flow\Mvc\Controller\ControllerContext;

class StornoInvoiceService
{
    const STORNO_NAMESPACE = 'KayStrobach_Invoice_InvoiceSettings_StornoBilling';

    /**
     * @Flow\Inject()
     * @var InvoiceRepository
     */
    protected InvoiceRepository $invoiceRepository;

    /**
     * @Flow\Inject()
     * @var InvoiceFactory
     */
    protected InvoiceFactory $invoiceFactory;

    public function makeStornoInvoice(Invoice $invoice, ControllerContext $controllerContext = null, \Closure $callback = null, bool $keepChangeable = false): Invoice
    {
        $newInvoice = $this->makeBaseObject($invoice);
        $newInvoice->setType('StornoBilling');
        $this->invoiceFactory->setTitle($newInvoice);
        $this->invoiceRepository->add($newInvoice);
        $this->invoiceFactory->setNumberDefaults($newInvoice);

        $newInvoice = $this->cloneSubRecords($invoice, $newInvoice);

        if ($callback instanceof \Closure) {
            $callback($newInvoice);
        }

        $newInvoice->setChangeable(true);
        if ($keepChangeable !== true) {
            $newInvoice->setChangeable(false);
            $newInvoice->createDocument($controllerContext, true);
        }

        $invoice->setStornoInvoice($newInvoice);
        $this->invoiceRepository->updateAndPersist($invoice);

        return $newInvoice;
    }

    protected function makeBaseObject(Invoice $invoice): Invoice
    {
        $newTotal = new MoneyEmbeddable();
        $newTotal->setValue($invoice->getTotal()->getValue() * -1);
        $newTotal->setCurrency($invoice->getTotal()->getCurrency());

        $newTotalNoTaxes = new MoneyEmbeddable();
        $newTotalNoTaxes->setValue($invoice->getTotalNoTaxes()->getValue() * -1);
        $newTotalNoTaxes->setCurrency($invoice->getTotalNoTaxes()->getCurrency());

        $newInvoice = $invoice->makePartialClone();
        $newInvoice->setTotal($newTotal);
        $newInvoice->setTotalNoTaxes($newTotalNoTaxes);
        $newInvoice->setTitle('Stornorechnung');
        $newInvoice->setSubTitle('zu ' . $invoice->getNumber()->getCombinedNumber());

        return $newInvoice;
    }

    protected function cloneSubRecords(Invoice $invoice, Invoice $newInvoice): Invoice
    {
        foreach ($invoice->getTaxRecords() as $taxRecord) {
            $newTaxRecord = new TaxRecord();
            $newTaxRecord->setInvoice($newInvoice);
            $newTaxRecord->setTaxRate($taxRecord->getTaxRate());
            $newTaxRecord->getSum()->setValue($taxRecord->getSum()->getValue() * -1);
            $newInvoice->getTaxRecords()->add($newTaxRecord);
        }

        /** @var InvoiceItem $invoiceItem */
        foreach ($invoice->getInvoiceItems() as $invoiceItem) {
            $newInvoiceItem = $invoiceItem->makePartialClone();
            $newInvoiceItem->getSinglePrice()->setValue($invoiceItem->getSinglePrice()->getValue() * -1);
            $newInvoiceItem->setInvoice($newInvoice);
            $newInvoice->getInvoiceItems()->add($newInvoiceItem);
        }
        $newInvoice->calculateTotal();
        /** @var AccountingRecord $accountingRecord */
        foreach ($invoice->getAccountingRecords() as $accountingRecord) {
            $newAccountingRecord = clone $accountingRecord;
            $newAccountingRecord->setShouldOrHave('S');
            $newAccountingRecord->setInvoice($newInvoice);
            $newAccountingRecord->setDueDate(null);
            $newAccountingRecord->setBelegfeld2($newInvoice->getNumber()->getCombinedNumber());
            $newAccountingRecord->setText($accountingRecord->getText() . ',Storno ' . $invoice->getNumber()->getCombinedNumber());
            $newInvoice->getAccountingRecords()->add($newAccountingRecord);
        }

        return $newInvoice;
    }
}
