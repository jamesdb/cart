<?php

namespace jamesdb\Cart\Event;

use jamesdb\Cart\Cart;
use jamesdb\Cart\CartItem;
use jamesdb\Cart\Event\AbstractCartEvent;

class CartItemAddEvent extends AbstractCartEvent
{
    /**
     * Return the Event name.
     *
     * @return string
     */
    public function getName()
    {
        return 'cart.add';
    }
}
