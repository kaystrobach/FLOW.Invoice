<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Exception;

/**
 * Thrown when an invoice that already has a number (or a rendered document) would lose it.
 * Invoice numbers must never be released: corrections are done with a cancellation invoice (Storno).
 */
class InvoiceLockedException extends \LogicException
{
}
