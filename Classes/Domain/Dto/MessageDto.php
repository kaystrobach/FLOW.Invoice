<?php

namespace KayStrobach\Invoice\Domain\Dto;

use KayStrobach\Invoice\Domain\Model\Invoice;
use Neos\Flow\Annotations as Flow;
class MessageDto
{
    /**
     * @Flow\Validate(type="NotEmpty")
     * @var Invoice
     */
    protected Invoice $invoice;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected string $message;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="EmailAddress")
     * @var string
     */
    protected string $to = '';

    /**
     * @var string
     */
    protected string $cc = '';

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    public function getCc(): string
    {
        return $this->cc;
    }

    public function setCc(string $cc): void
    {
        $this->cc = $cc;
    }
}
