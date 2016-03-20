<?php

namespace jamesdb\Cart;

use SebastianBergmann\Money\Currency;

interface CurrencyAwareInterface
{
    /**
     * Set the Currency.
     *
     * @param \SebastianBergmann\Money\Currency $currency
     */
    public function setCurrency(Currency $currency);

    /**
     * Return the Currency.
     *
     * @return \SebastianBergmann\Money\Currency $currency
     */
    public function getCurrency();
}
