<?php

namespace KayStrobach\Invoice\Command;

use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use Neos\Flow\Cli\CommandController;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;

/**
 * @Flow\Scope("singleton")
 */
class InvoiceCommandController extends CommandController
{
    #[Flow\Inject()]
    protected InvoiceRepository $invoiceRepository;

    #[Flow\Inject()]
    protected PersistenceManagerInterface $persistenceManager;

    #[Flow\Inject()]
    protected ResourceManager $resourceManager;

    public function statusCommand(Invoice $invoice)
    {
        $this->outputLine($invoice->isChangeable() ? 1 : 0);
    }

    public function resetCommand(Invoice $invoice)
    {
        $invoice->setChangeable(true);
        $invoice->getNumber()->resetNumber();

        $resource = $invoice->getOriginalResource();
        if ($resource !== null) {
            $this->resourceManager->deleteResource($resource);
            $invoice->resetOriginalResource();
        }


        $this->invoiceRepository->update($invoice);
        $this->persistenceManager->persistAll();
    }
}
