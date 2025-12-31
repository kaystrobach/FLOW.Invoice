<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Service;

use DateTime;
use horstoeko\zugferd\ZugferdDocumentPdfBuilder;
use KayStrobach\Invoice\Domain\Model\Invoice;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;

class CreateCompleteElectronicInvoiceService
{
    /**
     * @Flow\Inject()
     */
    protected CreateInvoicePdfService $createInvoicePdfService;

    /**
     * @Flow\Inject()
     */
    protected CreateZugpferdInvoiceService $createZugpferdInvoiceService;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    public function render(Invoice $invoice, bool $hideSinglePrice = false, ?string $watermark = null): string
    {
        $pdfContent = $this->createInvoicePdfService->renderPdf($invoice, $hideSinglePrice, $watermark);
        $document = $this->createZugpferdInvoiceService->renderDocument($invoice);

        $pdfDocument = new ZugferdDocumentPdfBuilder($document, $pdfContent);
        return $pdfDocument->generateDocument()->downloadString();
    }

    public function addRenderedPdfToInvoice(Invoice $invoice)
    {
        $now = new DateTime('now');
        $filename = sprintf(
            '%s.pdf',
            $invoice->getNumber()
        );

        $content = $this->render($invoice);

        if ($content !== null) {
            $resource = $this->resourceManager->importResourceFromContent(
                $content,
                $filename
            );
            $invoice->setOriginalResource($resource);
        } else {
            throw new \RuntimeException('Es gab ein Problem beim Erstellen der Rechnung, die Rechnung war leer');
        }
    }
}
