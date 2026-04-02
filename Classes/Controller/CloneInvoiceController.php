<?php

namespace KayStrobach\Invoice\Controller;

use Neos\Flow\Annotations as Flow;

use KayStrobach\Backend\Controller\AbstractPageRendererController;
use KayStrobach\Invoice\Domain\Dto\CloneInvoiceDto;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use KayStrobach\Invoice\Service\CloneInvoiceService;


class CloneInvoiceController extends AbstractPageRendererController
{
    /**
     * @Flow\Inject
     * @var CloneInvoiceService
     */
    protected CloneInvoiceService $cloneInvoiceService;

    /**
     * @Flow\Inject
     * @var InvoiceRepository
     */
    protected InvoiceRepository $invoiceRepository;

    public function prepareCloneAction(Invoice $invoice): void
    {
        $dto = new CloneInvoiceDto($invoice);
        $this->view->assign('dto', $dto);
    }

    public function cloneAction(CloneInvoiceDto $dto): void
    {
        $invoice = $dto->getInvoice();
        $newInvoice = $this->cloneInvoiceService->clone($invoice);
        $newInvoice->getPeriodOfPerformance()->setStart($dto->getPeriodOfPerformanceStart());
        $newInvoice->getPeriodOfPerformance()->setEnd($dto->getPeriodOfPerformanceEnd());
        $this->invoiceRepository->add($newInvoice);

        $this->addFlashMessage($invoice->getNumber()->getCombinedNumber() . ' wurde kopiert');

        $this->redirect(
            'edit',
            'Standard',
            null,
            [
                'object' => $newInvoice
            ]
        );
    }
}
