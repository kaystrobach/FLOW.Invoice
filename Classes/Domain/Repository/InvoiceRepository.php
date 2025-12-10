<?php
namespace KayStrobach\Invoice\Domain\Repository;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\VisualSearch\Domain\Repository\SearchableRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

/**
 * @Flow\Scope("singleton")
 */
class InvoiceRepository extends SearchableRepository
{
    /**
     * spezifies the default search used in visualsearch with that repository
     *
     * @var string
     */
    protected $defaultSearchName = 'KayStrobach_Invoice_Invoice';

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'date' => QueryInterface::ORDER_DESCENDING,
        'number.prefix' => QueryInterface::ORDER_DESCENDING,
        'number.combinedNumber' => QueryInterface::ORDER_DESCENDING
    ];

    public function newWithPrefix(string $prefix): Invoice
    {
        $invoice = new Invoice();
        $invoice->getNumber()->setPrefix($prefix);
        $this->add($invoice);
        return $invoice;
    }

    public function add($object): void
    {
        if ($object instanceof Invoice) {
            $object->calculateTotal();
            $this->_em->transactional(
                static function (EntityManager $em) use ($object) {
                    try {
                        $maxId = $em->createQueryBuilder()
                            ->select('MAX(e.number.combinedNumber)')
                            ->from(Invoice::class, 'e')
                            ->where('e.number.prefix = ?1')
                            ->setParameter(1, $object->getNumber()->getPrefix())
                            ->getQuery()
                            ->getSingleScalarResult();
                        $object->getNumber()->setNumber(1 + (int)$maxId);
                    } catch (NoResultException $exception) {
                        $object->getNumber()->setNumber(1);
                    }
                    $em->persist($object);
                    $em->flush($object);
                }
            );
        }
    }

    public function update($object): void
    {

        if ($object instanceof Invoice) {
            $object->calculateTotal();
        }
        parent::update($object);
    }

    public function updateAndPersist(Invoice $object)
    {
        $this->update($object);
        $this->persistenceManager->persistAll();
    }

    /**
     * @return \Neos\Flow\Persistence\QueryResultInterface
     */
    public function findWithoutAccountingRecords(): QueryResultInterface
    {
        $q = $this->createQuery();
        $qb = $q->getQueryBuilder();
        $qb->select('i')->from(Invoice::class, 'i')->where('i.accountingRecords is empty');
        return $q->execute();
    }
}
