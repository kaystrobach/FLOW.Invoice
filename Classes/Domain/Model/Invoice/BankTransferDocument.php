<?php
/**
 * Created by kay.
 */

namespace KayStrobach\Invoice\Domain\Model\Invoice;

use KayStrobach\Invoice\Domain\Model\Invoice;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\ResourceManagement\Exception;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;

/**
 * @Flow\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
class BankTransferDocument
{
    /**
     * @ORM\ManyToOne(inversedBy="bankTransferDocuments")
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var PersistentResource
     * @ORM\OneToOne(cascade={"all"})
     */
    protected $originalResource;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var bool
     */
    protected $toBePrinted;

    /**
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @ORM\ManyToOne(cascade={"persist"})
     * @var \KayStrobach\Invoice\Domain\Model\SettlementDate
     */
    protected $settlementDate;

    /**
     * @Flow\Inject()
     * @var ResourceManager
     */
    protected $resourceManager;

    public function __construct(\DateTime $creationDate = null)
    {
        if ($creationDate === null) {
            $this->creationDate = new \DateTime('now');
        }
    }

    /**
     * @return Invoice
     */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    /**
     * @param Invoice $invoice
     */
    public function setInvoice(Invoice $invoice): void
    {
        $this->invoice = $invoice;
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
    public function setOriginalResource(PersistentResource $originalResource = null): void
    {
        $this->originalResource = $originalResource;
    }

    /**
     * @param string $path
     * @return PersistentResource
     * @throws Exception
     */
    public function importResourceFromPath(string $path): PersistentResource
    {
        $this->originalResource = $this->resourceManager->importResource($path);
        $this->title = $this->originalResource->getFilename();
        return $this->originalResource;
    }


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }

    /**
     * @return bool
     */
    public function isToBePrinted(): bool
    {
        return $this->toBePrinted;
    }

    /**
     * @param bool $toBePrinted
     */
    public function setToBePrinted(bool $toBePrinted): void
    {
        $this->toBePrinted = $toBePrinted;
    }

    /**
     * @return \KayStrobach\Invoice\Domain\Model\SettlementDate
     */
    public function getSettlementDate(): \KayStrobach\Invoice\Domain\Model\SettlementDate
    {
        return $this->settlementDate;
    }

    /**
     * @param \KayStrobach\Invoice\Domain\Model\SettlementDate $settlementDate
     */
    public function setSettlementDate(\KayStrobach\Invoice\Domain\Model\SettlementDate $settlementDate): void
    {
        $this->settlementDate = $settlementDate;
    }

    public function getEpcQrCodeData()
    {
        return $this->invoice->getEpcQrCodeData($this->getSettlementDate()->getAmount());
    }
}
