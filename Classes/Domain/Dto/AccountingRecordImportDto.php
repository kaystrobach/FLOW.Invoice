<?php
/**
 * Created by kay.
 */

namespace KayStrobach\Invoice\Domain\Dto;


use Doctrine\Common\Collections\ArrayCollection;
use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\Domain\Repository\AccountingRecordRepository;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\Annotations as Flow;

class AccountingRecordImportDto
{
    /**
     * @var PersistentResource
     */
    protected $originalResource;

    /**
     * @var ArrayCollection<AccountingRecord>
     */
    protected $accountingRecords;

    /**
     * @Flow\Inject
     * @var AccountingRecordRepository
     */
    protected $accountingRecordRepository;

    public function __construct()
    {
        $this->accountingRecords = new ArrayCollection();
    }

    /**
     * @return PersistentResource
     */
    public function getOriginalResource(): ?PersistentResource
    {
        return $this->originalResource;
    }

    /**
     * @param PersistentResource $originalResource
     */
    public function setOriginalResource(PersistentResource $originalResource): void
    {
        $this->originalResource = $originalResource;
    }

    /**
     * @return ArrayCollection<AccountingRecord>
     */
    public function getAccountingRecords(): ArrayCollection
    {
        return $this->accountingRecords;
    }

    /**
     * @param ArrayCollection<AccountingRecord> $accountingRecords
     */
    public function setAccountingRecords(ArrayCollection $accountingRecords): void
    {
        $this->accountingRecords = $accountingRecords;
    }

    public function fetchData()
    {
        /** @var resource $stream */
        $stream = $this->originalResource->getStream();

        while ($csvline = fgetcsv($stream, $length = 0, $delimiter = ';', $enclosure = '"', $escape = '\\')) {
            $accountingRecords = $this->accountingRecordRepository->findByBelegfeld1AndDateAndAmount(
                $csvline[10],
                (float)str_replace(',', '.', $csvline[0])
            );
            if (($accountingRecords !== null) && ($accountingRecords->count() > 0)) {
                foreach ($accountingRecords as $accountingRecord) {
                    $this->accountingRecords->add($accountingRecord);
                }
            }
        }
    }
}
