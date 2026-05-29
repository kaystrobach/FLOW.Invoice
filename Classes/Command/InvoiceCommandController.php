<?php

namespace KayStrobach\Invoice\Command;

use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use Neos\Flow\Cli\CommandController;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * @Flow\Scope("singleton")
 */
class InvoiceCommandController extends CommandController
{
    #[Flow\Inject()]
    protected InvoiceRepository $invoiceRepository;

    #[Flow\Inject()]
    protected PersistenceManagerInterface $persistenceManager;

    public function resetCommand(Invoice $invoice)
    {
        $invoice->setChangeable(true);
        $invoice->getNumber()->resetNumber();
        // $invoice->resetOriginalResource();

        $this->invoiceRepository->update($invoice);
        $this->persistenceManager->persistAll();
    }
}
