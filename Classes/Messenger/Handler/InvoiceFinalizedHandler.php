<?php

namespace KayStrobach\Invoice\Messenger\Handler;
use KayStrobach\Invoice\Messenger\Message\InvoiceFinalizedMessage;
use KayStrobach\Invoice\Service\CreateInvoicePdfService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Neos\Flow\Annotations as Flow;

#[AsMessageHandler]
class InvoiceFinalizedHandler
{
    /**
     * @FLow\Inject
     * @var CreateInvoicePdfService
     */
    protected CreateInvoicePdfService $createPdfInvoiceService;

    public function __invoke(InvoiceFinalizedMessage $message)
    {
        $this->createPdfInvoiceService->render($message->getInvoice());
    }
}
