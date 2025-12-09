<?php

namespace KayStrobach\Invoice\Domain\Model\DailyClosing;

use KayStrobach\Invoice\Domain\Model\DailyClosing;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 */
class Download
{
    /**
     * @ORM\ManyToOne(inversedBy="downloads")
     * @var DailyClosing
     */
    protected $dailyClosing;

    /**
     * @ORM\ManyToOne()
     * @var \Neos\Flow\Security\Account
     */
    protected $account;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @return DailyClosing
     */
    public function getDailyClosing(): DailyClosing
    {
        return $this->dailyClosing;
    }

    /**
     * @param DailyClosing $dailyClosing
     */
    public function setDailyClosing(DailyClosing $dailyClosing): void
    {
        $this->dailyClosing = $dailyClosing;
    }

    /**
     * @return \Neos\Flow\Security\Account
     */
    public function getAccount(): \Neos\Flow\Security\Account
    {
        return $this->account;
    }

    /**
     * @param \Neos\Flow\Security\Account $account
     */
    public function setAccount(\Neos\Flow\Security\Account $account): void
    {
        $this->account = $account;
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
}
