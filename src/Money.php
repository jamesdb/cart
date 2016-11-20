<?php

namespace jamesdb\Cart;

use Money as MoneyPHP;

class Money implements CurrencyAwareInterface
{
    use CurrencyAwareTrait;

    /**
     * @var \Money\Money
     */
    protected $money;

    /**
     * Constructor.
     *
     * @param integer         $total
     * @param \Money\Currency $money
     */
    public function __construct($total = 0, MoneyPHP\Currency $currency)
    {
        $this->money = new MoneyPHP\Money($total, $currency);
    }

    /**
     * @return \Money\Money
     */
    public function getMoney()
    {
        return $this->money;
    }
}
