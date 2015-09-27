<?php

namespace jamesdb\Cart;

use jamesdb\Cart\CartItem;
use jamesdb\Cart\Event as CartEvent;
use jamesdb\Cart\Exception as CartException;
use jamesdb\Cart\Storage\StorageInterface;
use League\Event\Emitter;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

class Cart
{
    /**
     * Cart Identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * @var \jamesdb\Cart\Contract\StorageInterface
     */
    protected $storage;

    /**
     * Cart Contents.
     *
     * @var array
     */
    protected $contents = [];

    /**
     * Currency.
     *
     * @var \SebastianBergmann\Money\Currency
     */
    protected $currency;

    /**
     * Event Emitter.
     *
     * @var \League\Event\Emitter
     */
    protected $eventEmitter;

    /**
     * Constructor.
     *
     * @param string                         $identifier
     * @param \jamesdb\Cart\StorageInterface $storage
     */
    public function __construct($identifier, StorageInterface $storage)
    {
        $this->identifier   = $identifier;
        $this->storage      = $storage;
        $this->contents     = $this->storage->get($identifier) ?: [];
        $this->currency     = $this->currency ?: new Currency('GBP');
        $this->eventEmitter = $this->eventEmitter ?: new Emitter();
    }

    /**
     * Return the Cart identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the Currency.
     *
     * @param \SebastianBergmann\Money\Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Return the Currency.
     *
     * @return \SebastianBergmann\Money\Currency $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Add an Event Listener to the Emitter.
     *
     * @param  string          $eventName
     * @param  callable|object $listener
     *
     * @return void
     */
    public function addEventListener($eventName, $listener)
    {
        $this->eventEmitter->addListener($eventName, $listener);
    }

    /**
     * Returns the Event Emitter.
     *
     * @return \League\Event\Emitter
     */
    public function getEventEmitter()
    {
        return $this->eventEmitter;
    }

    /**
     * Add an item.
     *
     * @param  \jamesdb\Cart\CartItem $item
     *
     * @return string
     */
    public function add(CartItem $item)
    {
        $rowId = $item->getRowId();

        if ($row = $this->item($rowId)) {
            $row->quantity += $item->quantity;
        } else {
            $this->contents[$rowId] = $item;
        }

        $this->storage->store($this->getIdentifier(), $this->contents);

        $this->getEventEmitter()->emit(new CartEvent\CartItemAddEvent($this, $item));

        return $rowId;
    }

    /**
     * Remove an item.
     *
     * @param  string $rowId
     *
     * @throws \jamesdb\Cart\Exception\CartRemoveItemException
     *
     * @return boolean
     */
    public function remove($rowId)
    {
        $item = $this->item($rowId);

        if ($item === null) {
            throw new CartException\CartItemRemoveException(
                sprintf('No such item with rowid (%s).', $rowId)
            );
        }

        unset($this->contents[$rowId]);

        $this->storage->store($this->getIdentifier(), $this->contents);

        $this->getEventEmitter()->emit(new CartEvent\CartItemRemoveEvent($this, $item));

        return true;
    }

    /**
     * Update an item stored in the Cart.
     *
     * @param  string $rowId
     * @param  array  $data
     *
     * @throws \jamesdb\Cart\Exception\CartUpdateException
     *
     * @return boolean
     */
    public function update($rowId, array $data = [])
    {
        $row = $this->item($rowId);

        if ($row === null) {
            throw new CartException\CartItemUpdateException(
                sprintf('Could not update item (%s).', $rowId)
            );
        }

        foreach ($data as $key => $value) {
            $row->{$key} = $value;
        }

        $this->getEventEmitter()->emit(new CartEvent\CartItemUpdateEvent($this, $row));

        return true;
    }

    /**
     * Get a specific item from the cart.
     *
     * @param  string $rowId
     *
     * @return array|null
     */
    public function item($rowId)
    {
        if (array_key_exists($rowId, $this->contents)) {
            return $this->contents[$rowId];
        }

        return null;
    }

    /**
     * Clear the cart contents.
     *
     * @return void
     */
    public function clear()
    {
        $this->storage->clear($this->getIdentifier());

        $this->contents = [];
    }

    /**
     * Return items.
     *
     * @return array
     */
    public function items()
    {
        return $this->contents;
    }

    /**
     * Return a filtered array of cart items.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return array
     */
    public function filter($key, $value)
    {
        return array_filter($this->contents, function(CartItem $item) use ($key, $value) {
            if ((isset($item[$key])) && ($item[$key] === $value)) {
                return $item;
            }
        });
    }

    /**
     * Return the total amount of unique items.
     *
     * @return integer
     */
    public function getTotalUniqueItems()
    {
        return count($this->contents);
    }

    /**
     * Return the total amount of items.
     *
     * @return integer
     */
    public function getTotalItems()
    {
        return array_sum(
            array_map(function(CartItem $item) {
                return $item->quantity;
            }, $this->contents)
        );
    }

    /**
     * Returns whether the cart is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return ($this->getTotalUniqueItems() === 0);
    }

    /**
     * Return the price including tax.
     *
     * @return integer
     */
    public function getTotalPrice()
    {
        $total = new Money(array_sum(
            array_map(function(CartItem $item) {
                return $item->getPrice($this->getCurrency())->getAmount();
            }, $this->contents)
        ), $this->getCurrency());

        return $total->getConvertedAmount();
    }

    /**
     * Return the price excluding tax.
     *
     * @return integer
     */
    public function getTotalPriceExcludingTax()
    {
        $total = new Money(array_sum(
            array_map(function(CartItem $item) {
                return $item->getPriceExcludingTax($this->getCurrency())->getAmount();
            }, $this->contents)
        ), $this->getCurrency());

        return $total->getConvertedAmount();
    }

    /**
     * Return the carts total tax.
     *
     * @return integer
     */
    public function getTotalTax()
    {
        $total = new Money(array_sum(
            array_map(function(CartItem $item) {
                return $item->getTax($this->getCurrency())->getAmount();
            }, $this->contents)
        ), $this->getCurrency());

        return $total->getConvertedAmount();
    }
}
