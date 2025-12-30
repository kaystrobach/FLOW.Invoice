<?php

namespace KayStrobach\Invoice\Service;

use KayStrobach\Invoice\Domain\Model\Invoice;
use Neos\Flow\Annotations as Flow;

class SendInvoiceService
{

    /**
     * @param Invoice $invoice
     * @return void
     * @Flow\Signal
     *
     * will lateron be refactored to use a real message, so that we can do that async ...
     */
    public function emitInvoiceShouldBeSendNow(Invoice $invoice): void {}
}
