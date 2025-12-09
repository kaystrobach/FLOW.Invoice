<?php
namespace KayStrobach\Invoice\Domain\Repository;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use KayStrobach\Invoice\Domain\Model\SettlementDate;
use KayStrobach\VisualSearch\Domain\Repository\SearchableRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;

/**
 * @Flow\Scope("singleton")
 */
class AccountingRecordRepository extends SearchableRepository
{
    /**
     * spezifies the default search used in visualsearch with that repository
     *
     * @var string
     */
    protected $defaultSearchName = 'KayStrobach_Invoice_AccountingRecord';

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'dueDate' => QueryInterface::ORDER_DESCENDING,
        'invoice.numberPrefix' => QueryInterface::ORDER_DESCENDING,
        'invoice.number' => QueryInterface::ORDER_DESCENDING
    ];

    /**
     * @Flow\Inject()
     * @var SettlementDateRepository
     */
    protected $settlementDateRepository;

    public function findAllByDate(\DateTime $date)
    {
        $q = $this->createQuery();

        $date->setTime(0, 0);
        $dateEndObject = clone $date;
        $dateEndObject->setTime(23, 59, 59);

        $q->matching(
            $q->logicalAnd(
                [
                    $q->greaterThanOrEqual(
                        'invoice.date',
                        $date
                    ),
                    $q->lessThanOrEqual(
                        'invoice.date',
                        $dateEndObject
                    ),
                ]
            )
        );
        return $q->execute();
    }

    public function findByBelegfeld1AndDateAndAmount($belegfeld1, $amount)
    {
        /** @var SettlementDate $settlementDate */
        $settlementDate = $this->settlementDateRepository->findByNumberCompleteAndAmount($belegfeld1, $amount);
        if ($settlementDate === null) {
            return null;
        }

        $q = $this->createQuery();

        $date1 = clone $settlementDate->getDueDate();
        $date1->setTime( 0, 0, 0);
        $date2 = clone $settlementDate->getDueDate();
        $date2->setTime(23, 59, 59);

        $this->logger->debug('findByBelegfeld1AndDateAndAmount', [$belegfeld1, $amount]);

        $q->matching(
            $q->logicalAnd(
                [
                    $q->equals(
                        'invoice.numberComplete',
                        $belegfeld1
                    ),
                    $q->greaterThanOrEqual(
                        'dueDate',
                        $date1
                    ),
                    $q->lessThanOrEqual(
                        'dueDate',
                        $date2
                    )
                ]
            )
        );

        return $q->execute();
    }
}
