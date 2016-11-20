<?php

namespace jamesdb\Cart;

use Money\Currency;

trait CurrencyAwareTrait
{
    /**
     * @var \Money\Currency
     */
    protected $currency;

    /**
    * {@inheritdoc}
    */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
    * {@inheritdoc}
    */
    public function getCurrency()
    {
        return $this->currency;
    }
}
