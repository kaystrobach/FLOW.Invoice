<?php

namespace KayStrobach\Invoice\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use KayStrobach\Invoice\Domain\Model\DailyClosing\Download;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\Security\Account;

/**
 * @Flow\Entity
 */
class DailyClosing
{
    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(nullable=true)
     * @var \DateTime
     */
    protected $created;

    /**
     * @var PersistentResource
     * @ORM\OneToOne(cascade={"all"})
     */
    protected $originalResource;

    /**
     * @ORM\OneToMany(orphanRemoval=true, cascade={"all"}, mappedBy="dailyClosing")
     * @var Collection<Download>
     */
    protected $downloads;

    /**
     * @var float
     */
    protected $sum;

    public function __construct()
    {
        $this->downloads = new ArrayCollection();
        $this->created = new \DateTime('now');
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created = null): void
    {
        $this->created = $created;
    }

    /**
     * @return PersistentResource
     */
    public function getOriginalResource(): PersistentResource
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
     * @return Collection
     */
    public function getDownloads(): Collection
    {
        return $this->downloads;
    }

    /**
     * @param Collection $downloads
     */
    public function setDownloads(Collection $downloads): void
    {
        $this->downloads = $downloads;
    }

    /**
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * @param float $sum
     */
    public function setSum(float $sum): void
    {
        $this->sum = $sum;
    }

    public function addDownload(Account $account)
    {
        $download = new Download();
        $download->setDailyClosing($this);
        $download->setAccount($account);
        $download->setDate(new \DateTime('now'));
        $this->downloads->add($download);
    }
}
