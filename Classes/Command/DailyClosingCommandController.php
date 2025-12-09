<?php
namespace KayStrobach\Invoice\Command;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\Domain\Model\DailyClosing;
use KayStrobach\Invoice\Domain\Repository\AccountingRecordRepository;
use KayStrobach\Invoice\Domain\Repository\DailyClosingRepository;
use KayStrobach\Invoice\View\AccountingRecordsCsvView;
use KayStrobach\Invoice\View\AccountingRecordsDatevView;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Mvc\Exception;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;

/**
 * @Flow\Scope("singleton")
 */
class DailyClosingCommandController extends CommandController
{
    /**
     * @Flow\Inject()
     * @var AccountingRecordRepository
     */
    protected $accountingRecordRepository;

    /**
     * @Flow\Inject()
     * @var DailyClosingRepository
     */
    protected $dailyClosingRepository;

    /**
     * @Flow\Inject()
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param string date This argument is required, format Y-m-d
     * @return void
     * @throws \Exception
     */
    public function createCommand($date = null)
    {
        $dateObject = new \DateTime('now');
        $dateObject->sub(new \DateInterval('P1D'));

        if ($date !== null) {
            $dateObject = \DateTime::createFromFormat('Y-m-d', $date);
        }

        $this->outputLine('Performing export for ' . $dateObject->format('d.m.Y'));
        $records = $this->accountingRecordRepository->findAllByDate($dateObject);

        $this->outputLine('Found ' . $records->count() . ' records');

        $sum = 0;
        /** @var AccountingRecord $record */
        foreach ($records as $record) {
            if ($record->getShouldOrHave() === 'H') {
                $sum += $record->getAmount();
            } else {
                $sum -= $record->getAmount();
            }

        }

        $dc = new DailyClosing();
        $dc->setDate($dateObject);
        $dc->setSum($sum);
        $dc->setOriginalResource(
            $this->renderCsv(
                $records,
                $dateObject
            )
        );

        $this->outputLine(
            'Daily journal identifier: '
            . $this->persistenceManager->getIdentifierByObject($dc)
        );

        $this->dailyClosingRepository->add($dc);
        $this->persistenceManager->persistAll();
    }

    /**
     * @param $records
     * @param \DateTime $date
     * @return PersistentResource
     * @throws Exception
     * @throws \Neos\Flow\ResourceManagement\Exception
     */
    protected function renderCsv($records, \DateTime $date): PersistentResource
    {
        $view = new AccountingRecordsDatevView();
        $view->assign('values', $records);

        $view->setOption('startDate', $date);
        $view->setOption('endDate', $date);

        $resource = $this->resourceManager->importResourceFromContent(
            $view->render(),
            'EXTF_BO_AD_Buchungsstapel_' . $date->format('Y-m-d') . '_Tagesjournal.csv'
        );

        return $resource;
    }
}
