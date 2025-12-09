<?php

namespace KayStrobach\Invoice\Controller;

use KayStrobach\Backend\Controller\AbstractPageRendererController;
use KayStrobach\Invoice\Domain\Dto\AccountingRecordImportDto;
use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\Domain\Repository\AccountingRecordRepository;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;

class AccountingRecordImportController extends AbstractPageRendererController
{
    /**
     * @Flow\Inject()
     * @var AccountingRecordRepository
     */
    protected $accountingRecordRepository;

    /**
     * @Flow\Inject()
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    public function indexAction(): void
    {

    }

    public function reviewAction(AccountingRecordImportDto $dto): void
    {
        $dto->fetchData();
        $this->view->assign('dto', $dto);
    }

    /**
     * @param AccountingRecordImportDto $dto
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     */
    public function importAction(AccountingRecordImportDto $dto): void
    {
        //$dto->fetchData();
        $accountingRecords = $dto->getAccountingRecords();

        $this->addFlashMessage('Records:' . $accountingRecords->count());

        /** @var AccountingRecord $accountingRecord */
        foreach ($accountingRecords as $accountingRecord)
        {
            $invoice = $accountingRecord->getInvoice();
            $invoice->markSettlementDatePaidByDate(
                $accountingRecord->getDueDate(),
                true
            );
            $this->invoiceRepository->update($invoice);
            $this->accountingRecordRepository->update($accountingRecord);
        }

        $this->view->assign('dto', $dto);
        $this->redirect('index');
    }
}
