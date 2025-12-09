<?php

namespace KayStrobach\Invoice\Messenger\Message;

use KayStrobach\Invoice\Domain\Model\Invoice;

class InvoiceFinalizedMessage
{
    public function __construct(readonly private Invoice $invoice)
    {
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }
}
