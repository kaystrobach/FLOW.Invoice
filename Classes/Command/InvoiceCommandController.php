<?php

namespace KayStrobach\Invoice\Command;

use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use KayStrobach\Invoice\Service\CreateInvoicePdfService;
use Neos\Flow\Cli\CommandController;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Symfony\Component\Console\Question\Question;

/**
 * @Flow\Scope("singleton")
 */
class InvoiceCommandController extends CommandController
{
    #[Flow\Inject()]
    protected InvoiceRepository $invoiceRepository;

    #[Flow\Inject()]
    protected CreateInvoicePdfService $createInvoicePdfService;

    #[Flow\Inject()]
    protected PersistenceManagerInterface $persistenceManager;

    #[Flow\Inject()]
    protected ResourceManager $resourceManager;

    public function statusCommand(Invoice $invoice)
    {
        $this->outputLine($invoice->isChangeable() ? 1 : 0);
    }

    /**
     * Unlock a finalized invoice (e.g. after a broken PDF) – the invoice number is kept
     *
     * The rendered document is deleted and the invoice becomes changeable again. Finalizing it again
     * keeps the existing number. Releasing the number is not possible on purpose (gap in the sequence);
     * to only re-render the PDF use invoice:regenerateinvoicepdf.
     *
     * @param Invoice $invoice
     */
    public function resetCommand(Invoice $invoice)
    {
        $invoice->setChangeable(true);

        $resource = $invoice->getOriginalResource();
        if ($resource !== null) {
            $this->resourceManager->deleteResource($resource);
            $invoice->resetOriginalResource();
        }


        $this->invoiceRepository->update($invoice);
        $this->persistenceManager->persistAll();
        $this->outputLine(
            'Invoice %s unlocked, number kept.',
            [$invoice->getNumber()->getCombinedNumber()]
        );
    }

    public function regenerateInvoicePdfCommand(Invoice $invoice)
    {
        if ($invoice->getOriginalResource() !== null) {
            $answer = $this->output->askConfirmation(
                'PDF Exists, should it really be replaced? [y/N] ',
                false
            );
            if ($answer === false) {
                $this->outputLine('<error>Process stopped</error>');
                return 1;
            }
        }

        $this->createInvoicePdfService->addRenderedPdfToInvoice(
            $invoice,
            $this->createInvoicePdfService->renderPdf($invoice)
        );
        $this->invoiceRepository->update($invoice);
        $this->persistenceManager->persistAll();
        $this->outputLine('<success>Process successfull</success>');
        return 0;
    }
}
