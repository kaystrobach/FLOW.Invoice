<?php
namespace KayStrobach\Invoice\Domain\Model;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Comment
{
    /**
     * @var \DateTime
     */
    protected $date;
    /**
     * @var string
     * @Flow\Validate(type="text")
     * @Flow\Validate(type="notEmpty")
     * @ORM\Column(type="text", length=21844, nullable=true)
     */
    protected $comment;

    public function __construct()
    {
        $this->date = new \DateTime('now');
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}
