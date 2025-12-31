<?php
namespace KayStrobach\Invoice\Controller;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

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

class StandardController extends AbstractPageRendererController
{
    use CrudTrait;
    use TagsControllerTrait;

    #[Flow\Inject]
    protected MessageBusInterface $messageBus;

    /**
     * @Flow\Inject
     * @var SendInvoiceService
     */
    protected SendInvoiceService $sendInvoiceService;

    /**
     * @Flow\InjectConfiguration(path="Default.Invoice.numberPrefix")
     * @var string
     */
    protected $defaultNumberPrefix;

    /**
     * A list of formats and object names of the views which should render them.
     *
     * Example:
     *
     * array('html' => 'MyCompany\MyApp\MyHtmlView', 'json' => 'MyCompany\...
     *
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'zugpferd.xml' => InvoiceZugpferdView::class,
        'pdf' => InvoiceView::class,
    ];

    /**
     * @Flow\Inject
     * @var ObjectValidationService
     */
    protected ObjectValidationService $objectValidationService;

    public function getModelClassName()
    {
        return Invoice::class;
    }

    protected function renderView(): void
    {
        $this->initializeTagsForView($this->view);
        parent::renderView();
    }

    public function preNewAction(): void
    {
        $invoice = new Invoice();
        $invoice->setChangeable(true);
        $invoice->getNumber()->setPrefix($this->defaultNumberPrefix);
        $this->view->assign('object', $invoice);
        $this->view->assign('objectIsNew', true);
    }

    public function addInvoiceItemAction(Invoice $object)
    {
        $invoiceItem = new InvoiceItem();
        $invoiceItem->setInvoice($object);
        $invoiceItem->setUnit('C62');
        $invoiceItem->setSort($object->getInvoiceItems()->count() + 1);
        $invoiceItem->setAmount(1);
        $invoiceItem->setTax(19);
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

    public function addSettlementDateAction(Invoice $object)
    {
        $sd = new SettlementDate();
        $sd->setInvoice($object);
        $object->getSettlementDates()->add($sd);
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

    public function removesettlementdateAction(SettlementDate $item)
    {
        $object = $item->getInvoice();
        $object->getSettlementDates()->removeElement($item);
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
        if (!$object->isChangeable()) {
            $this->redirect(
                'edit',
                null,
                null,
                [
                    'object' => $object
                ]
            );
        }

        $object->setDate(new DateTime('now'));
        $this->invoiceFactory->setInvoiceNumber($object);
        $this->getRepository()->update($object);
        $this->messageBus->dispatch(new InvoiceFinalizedMessage($object));
        $this->getRepository()->update($object);
        $this->redirect(
            'sendMessage',
            null,
            null,
            [
                'object' => $object
            ]
        );
    }


    /**
     * @todo do this while creating the first invoice object before saving
     * @Flow\Inject
     * @var InvoiceFactory
     */
    protected InvoiceFactory $invoiceFactory;

    public function preUpdateAction(Invoice $object)
    {
        // $this->invoiceFactory->setInvoiceDefaultsFromEnv($object);
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

    /**
     * @Flow\ValidationGroups(validationGroups={"finalizeInvoice"})
     */
    public function checkInvoiceAction(Invoice $object)
    {
        $this->addFlashMessage('Prüfung erfolgreich');
        $this->redirect(
           'edit',
           null,
           null,
           ['object' => $object]
        );
    }

    public function updateCustomerDataAction(Invoice $object)
    {
        try {
            $this->invoiceFactory->triggerThirdPartyProcessesOnUpdate($object);
        } catch (\Exception $e) {
            $this->addFlashMessage('Es gab ein Problem: ' . $e->getMessage(), 'Fehler', Message::SEVERITY_ERROR);
            $this->errorAction();
        }

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

    public function renderInvoiceAction(Invoice $object)
    {
        $this->view->assign('object', $object);
    }

    public function renderFinalizeAction(Invoice $object)
    {
        $this->view->assign('isValid', $this->objectValidationService->isValid($object, ['finalizeInvoice']));
        $this->view->assign('object', $object);
    }

    public function sendMessageAction(Invoice $object)
    {
        $this->sendInvoiceService->emitInvoiceShouldBeSendNow($object);
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
