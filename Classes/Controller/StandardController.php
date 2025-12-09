<?php
namespace KayStrobach\Invoice\Controller;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use KayStrobach\Backend\Controller\AbstractPageRendererController;
use KayStrobach\Crud\Controller\Traits\CrudTrait;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\InvoiceItem;
use KayStrobach\Invoice\Messenger\Message\InvoiceFinalizedMessage;
use Neos\Flow\Annotations as Flow;
use Symfony\Component\Messenger\MessageBusInterface;

class StandardController extends AbstractPageRendererController
{

    #[Flow\Inject]
    protected MessageBusInterface $messageBus;

    //later use CrudTrait;
    use CrudTrait;

    /**
     * @Flow\InjectConfiguration(path="Default.Invoice.numberPrefix")
     * @var string
     */
    protected $defaultNumberPrefix;

    public function getModelClassName()
    {
        return Invoice::class;
    }

    public function preNewAction()
    {
        $invoice = new Invoice();
        $invoice->setChangeable(true);
        $invoice->setNumberPrefix($this->defaultNumberPrefix);
        $this->view->assign('object', $invoice);
    }

    public function addInvoiceItemAction(Invoice $object)
    {
        $invoiceItem = new InvoiceItem();
        $invoiceItem->setInvoice($object);
        $invoiceItem->setUnit('Pcs');
        $object->getInvoiceItems()->add($invoiceItem);
        $this->getRepository()->update($object);
        $this->redirect(
            'edit',
            null,
            null,
            [
                'object' => $object
            ]
        );
    }

    public function removeInvoiceItemAction(Invoice $object, InvoiceItem $item)
    {
        $object->getInvoiceItems()->removeElement($item);
        $this->getRepository()->update($object);
        $this->redirect(
            'edit',
            null,
            null,
            [
                'object' => $object
            ]
        );
    }

    public function finalizeAction(Invoice $object)
    {
        $object->setChangeable(false);
        $this->getRepository()->update($object);
        $this->messageBus->dispatch(new InvoiceFinalizedMessage($object));
        $this->redirect(
            'edit',
            null,
            null,
            [
                'object' => $object
            ]
        );
    }

    public function preUpdateAction(Invoice $object)
    {
        $this->getRepository()->update($object);
    }

    public function createBankTransfersAction(Invoice $object)
    {
        $object->makeBankTransferDocument();
        $this->getRepository()->update($object);
        $this->redirect(
            'edit',
            null,
            null,
            [
                'object' => $object
            ]
        );
    }

}
