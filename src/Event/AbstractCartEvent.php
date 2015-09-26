<?php

namespace jamesdb\Cart\Event;

use League\Event\AbstractEvent;
use jamesdb\Cart\Cart;
use jamesdb\Cart\CartItem;

abstract class AbstractCartEvent extends AbstractEvent
{
    /*
     * The cart.
     *
     * @var \jamesdb\Cart\Cart
     */
    protected $cart;

    /**
     * The added Item.
     *
     * @var \jamesdb\Cart\Item
     */
    protected $item;

    /**
     * Constructor.
     *
     * @param jamesdb\Cart\Cart      $cart
     * @param jamesdb\Cart\CartItem $item
     */
    public function __construct(Cart $cart, CartItem $item)
    {
        $this->cart = $cart;
        $this->item = $item;
    }

    /**
     * Get the Cart.
     *
     * @return \jamesdb\Cart\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Get the item.
     *
     * @return \jamesdb\Cart\CartItem
     */
    public function getItem()
    {
        return $this->item;
    }
}
