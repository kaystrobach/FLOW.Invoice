<?php

namespace KayStrobach\Invoice\Controller;
use fucodo\registry\Domain\Repository\RegistryEntryRepository;
use KayStrobach\Backend\Controller\AbstractPageRendererController;
use KayStrobach\Invoice\Domain\Dto\CreateInvoiceDto;
use KayStrobach\Invoice\Domain\Factory\InvoiceFactory;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use Neos\Flow\Annotations as Flow;

class InvoiceWizardController extends AbstractPageRendererController
{
    /**
     * @Flow\Inject
     * @var InvoiceFactory
     */
    protected InvoiceFactory $invoiceFactory;

    /**
     * @Flow\Inject
     * @var InvoiceRepository
     */
    protected InvoiceRepository $invoiceRepository;

    public function newAction()
    {
        $this->view->assign('dto', new CreateInvoiceDto());
    }

    /**
     * @var CreateInvoiceDto $dto
     */
    public function createAction(CreateInvoiceDto $dto)
    {
        // init the invoice
        // we need to determine the type here  ...
        $invoice = new Invoice();

        $this->invoiceFactory->setInvoiceDefaultsFromEnv($invoice, $dto->getType());

        $invoice->getOrder()->setOrderNumber($dto->getOrderNumber());

        $invoice->getCustomer()->setDeptorNumber($dto->getCustomerNumber());
        $invoice->setOfferNumber($dto->getOfferNumber());

        $invoice->setChangeable(true);

        // emit the signal, maybe used by crm to add even more data to the invoice
        $this->invoiceFactory->triggerThirdPartyProcessesOnUpdate($invoice);

        $this->invoiceRepository->add($invoice);
        $this->redirect(
            'edit',
            'Standard',
            null,
            [
                'object' => $invoice
            ]
        );
    }


}
