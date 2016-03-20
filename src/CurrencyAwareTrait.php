<?php

namespace jamesdb\Cart;

use SebastianBergmann\Money\Currency;

trait CurrencyAwareTrait
{
    /**
     * @var \SebastianBergmann\Money\Currency
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
