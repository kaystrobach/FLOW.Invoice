<?php

namespace KayStrobach\Invoice\Messenger\Handler;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Messenger\Message\InvoiceFinalizedMessage;
use KayStrobach\Invoice\Service\CreateInvoicePdfService;
use KayStrobach\Invoice\Service\SendInvoiceService;
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

    /**
     * @Flow\Inject
     * @var SendInvoiceService
     */
    protected SendInvoiceService $sendInvoiceService;

    public function __invoke(InvoiceFinalizedMessage $message)
    {
        $this->createPdfInvoiceService->render($message->getInvoice());
        $this->sendInvoiceService->emitInvoiceShouldBeSendNow($message->getInvoice());
    }
}
