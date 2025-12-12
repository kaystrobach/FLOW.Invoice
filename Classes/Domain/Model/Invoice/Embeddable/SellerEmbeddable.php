<?php

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 *
 */
class SellerEmbeddable extends AddressEmbeddable
{

}
