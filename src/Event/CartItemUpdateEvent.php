<?php

namespace jamesdb\Cart\Event;

use jamesdb\Cart\Cart;
use jamesdb\Cart\CartItem;
use jamesdb\Cart\Event\AbstractCartEvent;

class CartItemUpdateEvent extends AbstractCartEvent
{
    /**
     * Return the Event name.
     *
     * @return string
     */
    public function getName()
    {
        return 'cart.update';
    }
}
