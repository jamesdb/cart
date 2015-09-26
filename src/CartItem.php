<?php

namespace jamesdb\Cart;

use ArrayAccess;
use jamesdb\Cart\Exception\CartPropertyNotNumericException;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

class CartItem implements ArrayAccess
{
    /**
     * @var array
     */
    protected $item = [];

    /**
     * Constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $defaults = [
            'options'  => [],
            'price'    => 0,
            'quantity' => 1,
            'tax'      => 0
        ];

        $data = array_merge($defaults, $data);

        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Return the rowid of item.
     *
     * @return string
     */
    public function getRowId()
    {
        $rowId = $this->item;

        $ignoredProperties = ['quantity'];

        foreach ($ignoredProperties as $property) {
            if (array_key_exists($property, $rowId)) {
                unset($rowId[$property]);
            }
        }

        return md5(serialize($rowId));
    }

    /**
     * Set property key and value.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function set($key, $value)
    {
        $numeric = ['price', 'tax', 'quantity'];

        if (in_array($key, $numeric) && (! is_numeric($value))) {
            return $this->throwNotNumericException($key);
        }

        $this->item[$key] = $value;
    }

    /**
     * Get a property by key.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if ($key === 'rowid') {
            return $this->getRowId();
        }

        return $this->item[$key];
    }

    /**
     * Return item price excluding tax.
     *
     * @return \SebastianBergmann\Money\Money
     */
    public function getPriceExcludingTax(Currency $currency)
    {
        return (new Money($this->price, $currency))->multiply($this->quantity);
    }

    /**
     * Return item price including tax.
     *
     * @return \SebastianBergmann\Money\Money
     */
    public function getPrice(Currency $currency)
    {
        return (new Money($this->price + $this->tax, $currency))->multiply($this->quantity);
    }

    /**
     * Return the item tex.
     *
     * @return SebastianBergmann\Money\Money
     */
    public function getTax(Currency $currency)
    {
        return (new Money($this->tax, $currency))->multiply($this->quantity);
    }

    /**
     * Throws a CartPropertyNotNumericException.
     *
     * @param  string $key
     *
     * @throws \jamesdb\Cart\Exception\CartPropertyNotNumericException
     *
     * @return void
     */
    public function throwNotNumericException($key)
    {
        throw new CartPropertyNotNumericException(
            sprintf('The [%s] property must be numeric', $key)
        );
    }

    /**
     * Set property key and value.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Get a property by key.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Array Access get.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return isset($this->item[$offset]);
    }

    /**
     * Array Access get.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Array Access set.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Array Access unset.
     *
     * @param  string $key
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->item[$offset]);
    }
}
