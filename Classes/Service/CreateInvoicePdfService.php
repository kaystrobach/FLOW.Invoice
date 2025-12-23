<?php

namespace KayStrobach\Invoice\Service;

use DateTime;
use fucodo\registry\Domain\Repository\RegistryEntryRepository;
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

    /**
     * @Flow\Inject
     * @var RegistryEntryRepository
     */
    protected RegistryEntryRepository $registryEntryRepository;

    public function render(Invoice $invoice, bool $hideSinglePrice = false)
    {
        $this->addRenderedPdfToInvoice(
            $invoice,
            $this->renderPdf($invoice, $hideSinglePrice)
        );
    }

    public function addRenderedPdfToInvoice(Invoice $invoice, string $content)
    {
        $now = new DateTime('now');
        $filename = sprintf(
            'Rechnung.%s.%s.%s.pdf',
            $invoice->getNumber()->getPrefix(),
            $now->format('Y-m-d'),
            $invoice->getNumber()
        );
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

    public function renderPdf(Invoice $invoice, bool $hideSinglePrice = false, ?string $watermark = null): string
    {
        $view = new PdfTemplateView();
        // $view->setTemplatePathAndFilename('resource://KayStrobach.Invoice/Private/Documents/Invoice.html');
        if ($watermark !== null) {
            $view->setOption('watermarkText', $watermark);
        }
        $view->setTemplateRootPaths(
            [
                $this->registryEntryRepository->getValue('KayStrobach_Invoice_General', 'fluidInvoiceTemplateRootPaths'),
                'resource://KayStrobach.Invoice/Private/Templates'
            ]
        );
        $view->setLayoutRootPaths(
            [
                $this->registryEntryRepository->getValue('KayStrobach_Invoice_General', 'fluidInvoiceLayoutRootPaths'),
                'resource://KayStrobach.Invoice/Private/Layouts',
            ]
        );
        $view->setPartialRootPaths(
            [
                $this->registryEntryRepository->getValue('KayStrobach_Invoice_General', 'fluidInvoicePartialRootPaths'),
                'resource://KayStrobach.Invoice/Private/Partials',
            ]
        );
        $view->assign(
            'hideSinglePrice',
            $hideSinglePrice
        );
        $view->assign(
            'invoice',
            $invoice
        );
        $view->assign(
            'qrCodeData',
            $this->getEpcQrCodeData($invoice)
        );

        return $view->render('Invoice');
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
            $invoice->getSeller()->getReceiverBic(),  // Empfänger BIX
            $invoice->getSeller()->getReceiverName(), // Empfänger Name
            $invoice->getSeller()->getReceiverIban(), // Empfänger IBAN
            'EUR' . number_format($amount ?? $invoice->getTotal()->getValue(), 2, '.', ''), // Komplett Betrag für die Überweisung
            '',                   // Zweck vierstelliger Code
            '',
            $invoice->getNumber()->getCombinedNumber()
        ];
        return implode('\n', $data);
    }
}
