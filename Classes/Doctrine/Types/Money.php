<?php

namespace KayStrobach\Invoice\Doctrine\Types;

class Money
{
    private $amount;
    private $currencyCode;

    /**
     * @return \Money\Money
     */
    public function getValue()
    {
        return new \Money\Money($this->amount, new \Money\Currency($this->currencyCode));
    }

    /**
     * @param \Money\Money $money
     */
    public function setValue(\Money\Money $money)
    {
        $this->amount = $money->getAmount();
        $this->currencyCode = $money->getCurrency()->getCode();
    }
}
