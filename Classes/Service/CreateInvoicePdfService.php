<?php

namespace KayStrobach\Invoice\Service;

use DateTime;
use Neos\Flow\Annotations as Flow;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Pdf\View\PdfTemplateView;
use Neos\Flow\ResourceManagement\ResourceManager;

class CreateInvoicePdfService
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    public function render(Invoice $invoice, bool $hideSinglePrice = false)
    {
        $this->renderPdf($invoice, $hideSinglePrice);
    }

    public function renderPdf(Invoice $invoice, bool $hideSinglePrice = false)
    {
        $view = new PdfTemplateView();
        $view->setTemplatePathAndFilename('resource://KayStrobach.Invoice/Private/Documents/Invoice.html');
        $view->setLayoutRootPaths(
            ['resource://KayStrobach.Invoice/Private/Layouts']
        );
        $view->setTemplateRootPaths(
            ['resource://KayStrobach.Invoice/Private/Templates']
        );
        $view->setPartialRootPaths(
            ['resource://KayStrobach.Invoice/Private/Partials']
        );
        $view->assign(
            'hideSinglePrice',
            $hideSinglePrice
        );
        $view->assign(
            'invoice',
            $this
        );
        $view->assign(
            'qrCodeData',
            $this->getEpcQrCodeData($invoice)
        );

        $content = $view->render();
        $now = new DateTime('now');
        $filename = 'Rechnung.' . $invoice->getNumberPrefix() . $now->format('Y-m-d') . '-' . $invoice->getNumber() . '.pdf';
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

    /**
     * https://de.wikipedia.org/wiki/EPC-QR-Code
     */
    public function getEpcQrCodeData(Invoice $invoice, float $amount = null)
    {
        $data = [
            'BCD', // Service Tag
            '002', // Version
            '1',   // Zeichenkodierung UTF-8
            'SCT', // Sepa Credit Transfer
            $invoice->getReceiverBic(),  // Empfänger BIX
            $invoice->getReceiverName(), // Empfänger Name
            $invoice->getReceiverIban(), // Empfänger IBAN
            'EUR' . number_format($amount ?? $invoice->getTotal(), 2, '.', ''), // Komplett Betrag für die Überweisung
            '',                   // Zweck vierstelliger Code
            '',
            $invoice->getNumberComplete()
        ];
        return implode('\n', $data);
    }
}
