<?php
namespace KayStrobach\Invoice\Domain\Repository;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use KayStrobach\Invoice\Domain\Exception\InvoiceLockedException;
use KayStrobach\Invoice\Domain\Factory\InvoiceFactory;
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

    /**
     * @Flow\Inject
     * @var InvoiceFactory
     */
    protected InvoiceFactory $invoiceFactory;

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
            parent::add($object);
        }
    }

    /**
     * Invoices with a number or a rendered document must not be deleted, otherwise the number
     * sequence gets a gap (or, for the highest number, the number is assigned a second time).
     * Use a cancellation invoice instead.
     */
    public function remove($object): void
    {
        if ($object instanceof Invoice && !$object->isDeletable()) {
            throw new InvoiceLockedException(
                sprintf(
                    'Rechnung %s ist festgeschrieben und kann nicht gelöscht werden – bitte stornieren.',
                    $object->getNumber()->getCombinedNumber()
                ),
                1760000001
            );
        }
        parent::remove($object);
    }

    public function update($object): void
    {
        if ($object instanceof Invoice) {
            $object->calculateTotal();
            if (!$object->isLocked()) {
                $object->prePersistHandler();
            }
        }
        parent::update($object);
    }

    public function calculateInvoiceNumber(Invoice $object): string
    {
        $this->invoiceFactory->setInvoiceNumber($object);
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
