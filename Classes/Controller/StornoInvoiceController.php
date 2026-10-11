<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Controller;


use DateTime;
use KayStrobach\Backend\Controller\AbstractPageRendererController;
use KayStrobach\Crud\Controller\Traits\CrudTrait;
use KayStrobach\Invoice\Domain\Factory\InvoiceFactory;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\InvoiceItem;
use KayStrobach\Invoice\Domain\Model\SettlementDate;
use KayStrobach\Invoice\Messenger\Message\InvoiceFinalizedMessage;
use KayStrobach\Invoice\Service\ObjectValidationService;
use KayStrobach\Invoice\Service\SendInvoiceService;
use KayStrobach\Invoice\View\InvoiceView;
use KayStrobach\Invoice\View\InvoiceZugpferdView;
use KayStrobach\Tags\Traits\TagsControllerTrait;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Utility\ObjectAccess;
use Symfony\Component\Messenger\MessageBusInterface;

class StornoInvoiceController extends AbstractPageRendererController
{
    public function stornoInvoiceAction(Invoice $object): void
    {
        $cancellationInvoice = $object->getStornoInvoice();
        if ($cancellationInvoice === null) {
            if (!$object->isLocked() || $object->getOriginalInvoice() !== null) {
                $this->addFlashMessage(
                    'Nur festgeschriebene Rechnungen können storniert werden, Stornorechnungen nicht.',
                    'Fehler',
                    Message::SEVERITY_ERROR
                );
                $this->redirect('edit', 'Standard', null, ['object' => $object]);
            }

            // the cancellation invoice is finalized right away: number and document, no draft
            $cancellationInvoice = $object->makeCancellationInvoice(
                $this->getControllerContext(),
                null,
                false
            );
            $this->addFlashMessage(
                'Stornorechnung %s wurde erstellt',
                '',
                Message::SEVERITY_OK,
                [$cancellationInvoice->getNumber()->getCombinedNumber()]
            );
        }

        $this->redirect(
            'show',
            'Standard',
            null,
            [
                'object' => $cancellationInvoice
            ]
        );
    }
}
