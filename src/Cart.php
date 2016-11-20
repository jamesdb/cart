<?php

namespace jamesdb\Cart;

use jamesdb\Cart\CartItem;
use jamesdb\Cart\Event as CartEvent;
use jamesdb\Cart\Exception as CartException;
use jamesdb\Cart\Storage\StorageInterface;
use League\Event\Emitter;
use Money\Currency;

class Cart implements CurrencyAwareInterface
{
    use CurrencyAwareTrait;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var \jamesdb\Cart\Storage\StorageInterface
     */
    protected $storage;

    /**
     * Cart Contents.
     *
     * @var array
     */
    protected $contents = [];

    /**
     * Event Emitter.
     *
     * @var \League\Event\Emitter
     */
    protected $eventEmitter;

    /**
     * @var callback
     */
    protected $formatterCallback;

    /**
     * Constructor.
     *
     * @param string                                 $identifier
     * @param \jamesdb\Cart\Storage\StorageInterface $storage
     */
    public function __construct($identifier, StorageInterface $storage)
    {
        $this->identifier   = $identifier;
        $this->storage      = $storage;
        $this->eventEmitter = new Emitter();

        $this->setCurrency(new Currency('GBP'));

        $this->restore();
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
     * Set the formatter callback.
     *
     * @param callable $callback
     */
    public function setFormatterCallback(callable $callback)
    {
        $this->formatterCallback = $callback;
    }

    /**
     * Get the formatter callback.
     *
     * @throws \jamesdb\Cart\Exception\CartFormatterCallbackException
     *
     * @return callable
     */
    public function getFormatterCallback()
    {
        if (($this->formatterCallback === null) || (! is_callable($this->formatterCallback))) {
            throw new CartException\CartFormatterCallbackException('Invalid callback');
        }

        return $this->formatterCallback;
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

        if ($row = $this->getItem($rowId)) {
            $row->quantity += $item->quantity;
        } else {
            $this->contents[$rowId] = $item;
        }

        $this->storage->store($this->identifier, serialize($this->toArray()));

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
        $item = $this->getItem($rowId);

        if ($item === null) {
            throw new CartException\CartItemRemoveException(
                sprintf('No such item with rowid (%s).', $rowId)
            );
        }

        unset($this->contents[$rowId]);

        $this->storage->store($this->identifier, serialize($this->contents));

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
        $row = $this->getItem($rowId);

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
     * Clear the cart.
     *
     * @return void
     */
    public function clear()
    {
        $this->storage->clear($this->identifier);

        $this->contents = [];
    }

    /**
     * Get a specific item from the cart.
     *
     * @param  string $rowId
     *
     * @return \jamesdb\Cart\CartItem|null
     */
    public function getItem($rowId)
    {
        if (array_key_exists($rowId, $this->contents)) {
            return $this->contents[$rowId];
        }

        return null;
    }

    /**
     * Return items.
     *
     * @return array
     */
    public function getItems()
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

        return call_user_func($this->getFormatterCallback(), $total->getMoney());
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

        return call_user_func($this->getFormatterCallback(), $total->getMoney());
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

        return call_user_func($this->getFormatterCallback(), $total->getMoney());
    }

    /**
     * Restore the cart from storage.
     *
     * @throws \jamesdb\Cart\Exception\CartRestoreException
     *
     * @return boolean
     */
    public function restore()
    {
        $data = $this->storage->get($this->identifier);

        if (! empty($data)) {
            $data = unserialize($data);

            if (is_array($data) && is_string($data['id']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->contents[$item['id']] = new CartItem($item['data']);
                }

                return true;
            }

            throw new CartException\CartRestoreException(
                sprintf(
                    'Unable to restore cart [%s] from storage, ensure id is a string and the items are an array',
                    $this->identifier
                )
            );
        }

        return false;
    }

    /**
     * Export the cart to array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'    => $this->identifier,
            'items' => array_map(function (CartItem $item) {
                return $item->toArray();
            }, $this->contents)
        ];
    }
}
