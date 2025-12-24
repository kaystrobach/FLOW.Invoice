<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Service;

use KayStrobach\Invoice\Domain\Model\Invoice;
use Neos\Flow\Annotations as Flow;

class CreateCompleElectronicInvoiceService
{
    /**
     * @Flow\Inject()
     */
    protected CreateInvoicePdfService $createInvoicePdfService;

    /**
     * @Flow\Inject()
     */
    protected CreateZugpferdInvoiceService $createZugpferdInvoiceService;

    public function render(Invoice $invoice)
    {
        PdfZug::attachXmlToPdf($pdfFilename, $xml, $pdfFilename);
    }
}
