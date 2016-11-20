<?php

namespace jamesdb\Cart;

use Money\Currency;

interface CurrencyAwareInterface
{
    /**
     * Set the Currency.
     *
     * @param \Money\Currency $currency
     */
    public function setCurrency(Currency $currency);

    /**
     * Return the Currency.
     *
     * @return \Money\Currency $currency
     */
    public function getCurrency();
}
