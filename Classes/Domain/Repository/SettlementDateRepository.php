<?php
namespace KayStrobach\Invoice\Domain\Repository;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use KayStrobach\VisualSearch\Domain\Repository\SearchableRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;

/**
 * @Flow\Scope("singleton")
 */
class SettlementDateRepository extends SearchableRepository
{
    /**
     * spezifies the default search used in visualsearch with that repository
     *
     * @var string
     */
    protected $defaultSearchName = 'KayStrobach_Invoice_SettlementDate';

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'dueDate' => QueryInterface::ORDER_DESCENDING,
        'invoice.numberPrefix' => QueryInterface::ORDER_DESCENDING,
        'invoice.number' => QueryInterface::ORDER_DESCENDING
    ];

    /**
     * @param \Neos\Flow\Persistence\Doctrine\Query $queryObject
     * @param string $searchName
     * @return array
     */
    public function initializeFindByQuery($queryObject, $searchName)
    {
        $demands = [
            $queryObject->equals('invoice.stornoInvoice', null)
        ];
        return $demands;
    }

    public function findByNumberCompleteAndAmount(string $numberComplete, float $amount)
    {
        $q = $this->createQuery();

        $q->matching(
            $q->logicalAnd(
                [
                    $q->equals(
                        'invoice.numberComplete',
                        $numberComplete
                    ),
                    $q->equals(
                        'amount',
                        $amount
                    ),
                    $q->equals(
                        'paid',
                        false
                    )
                ]
            )
        );
        return $q->execute()->getFirst();
    }
}
