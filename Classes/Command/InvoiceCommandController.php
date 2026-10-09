<?php

namespace KayStrobach\Invoice\Command;

use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use KayStrobach\Invoice\Service\CreateInvoicePdfService;
use KayStrobach\Invoice\Service\InvoiceNumberSequenceAnalyzer;
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

    #[Flow\Inject()]
    protected EntityManagerInterface $entityManager;

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

    /**
     * List gaps and duplicates in the invoice number series
     *
     * Every series (prefix + postfix) is expected to run from 1 without gaps. Exit code 1 if a gap
     * or a duplicate was found.
     *
     * @param string|null $prefix only this prefix (e.g. R-2026-)
     * @return int
     */
    public function checkNumbersCommand(?string $prefix = null): int
    {
        $sql = 'SELECT number_prefix AS prefix, number_postfix AS postfix, number_number AS number,
                       CONCAT(number_combinednumber, \' (\', persistence_object_identifier, \')\') AS invoice
                FROM kaystrobach_invoice_domain_model_invoice
                WHERE number_number IS NOT NULL' . ($prefix !== null ? ' AND number_prefix = ?' : '');
        $rows = $this->entityManager->getConnection()->fetchAllAssociative($sql, $prefix !== null ? [$prefix] : []);
        $result = (new InvoiceNumberSequenceAnalyzer())->analyze($rows);

        $tableRows = [];
        $problems = false;
        foreach ($result as $series) {
            $problems = $problems || $series['gaps'] !== [] || $series['duplicates'] !== [];
            $tableRows[] = [
                $series['prefix'] . '…' . $series['postfix'],
                $series['count'],
                $series['highest'],
                $series['gaps'] === [] ? '–' : InvoiceNumberSequenceAnalyzer::compress($series['gaps']),
                $series['duplicates'] === [] ? '–' : implode(', ', array_keys($series['duplicates'])),
            ];
        }
        $this->output->outputTable($tableRows, ['Series', 'Invoices', 'Highest', 'Gaps', 'Duplicates']);

        foreach ($result as $series) {
            foreach ($series['duplicates'] as $number => $invoices) {
                $this->outputLine('<error>Duplicate %s%d%s:</error> %s', [$series['prefix'], $number, $series['postfix'], implode(', ', $invoices)]);
            }
        }
        return $problems ? 1 : 0;
    }
}
