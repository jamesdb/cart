<?php

namespace jamesdb\Cart;

use jamesdb\Cart\Identifier\StringIdentifier;
use jamesdb\Cart\Storage\StorageInterface;
use SebastianBergmann\Money\Currency;

class CartFactory
{
    /**
     * Returns a cart instance.
     *
     * @param string $identifier
     * @param string $store
     *
     * @return \jamesdb\Cart\Cart
     */
    public function newInstance($identifier, $store, $currency = null)
    {
        $store = sprintf('jamesdb\Cart\Storage\%s', $store);

        $cart = new Cart(new StringIdentifier($identifier), new $store());

        if (! is_null($currency)) {
            $currency = new Currency($currency);

            $cart->setCurrency($currency);
        }

        return $cart;
    }
}
