# Shopping Cart

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/jamesdb/cart/master.svg?style=flat-square)](https://travis-ci.org/jamesdb/cart)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/jamesdb/cart.svg?style=flat-square)](https://scrutinizer-ci.com/g/jamesdb/cart/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/jamesdb/cart.svg?style=flat-square)](https://scrutinizer-ci.com/g/jamesdb/cart)
[![Total Downloads](https://img.shields.io/packagist/dt/jamesdb/cart.svg?style=flat-square)](https://packagist.org/packages/jamesdb/cart)

A framework agnostic shopping cart package.

## Install

Via Composer

```bash
$ composer require jamesdb/cart
```

## Notes

### Money Value Objects

This package uses [moneyphp/money](https://github.com/moneyphp/money) (an implementation of Martin Fowler's money pattern). Floats are avoided due to them being ill-suited for monetary values. More information can be found in the links below:

* [http://martinfowler.com/eaaCatalog/money.html](http://martinfowler.com/eaaCatalog/money.html)
* [http://culttt.com/2014/05/28/handle-money-currency-web-applications/](http://culttt.com/2014/05/28/handle-money-currency-web-applications/)

Due to this when dealing with monetary values you will need to represent them as integers instead of floats.

An example of this can be found below.

```php
use jamesdb\Cart\CartItem;

$item = new CartItem([
    ...
    'price' => 1099, // Instead of £10.99 or $10.99 etc.
    ...
]);
```

## Setting Up

To setup a cart instance you need to pass an identifier and storage implementation to the cart constructor.

The currency defaults to 'GBP', if you want to change this you will need to pass your currency of choice into the ```setCurrency``` method as shown below.

A custom formatter callback can be setup via ```setFormatterCallback```, see [moneyphp formatters](http://moneyphp.org/en/latest/features/formatting.html) for more information.

```php
use jamesdb\Cart\Cart;
use jamesdb\Cart\Storage\NativeSessionDriver;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;

$cart = new Cart('cart', new NativeSessionDriver);
$cart->setCurrency(new Currency('GBP'));

$cart->setFormatterCallback(function ($money) {
    $currencies = new ISOCurrencies();
    $moneyFormatter = new DecimalMoneyFormatter($currencies);

    return $moneyFormatter->format($money); // outputs in decimal format.
});
```

Any storage implementation can be used as long as it implements the  ```jamesdb\Cart\Storage\StorageInterface```.

## Usage

### Adding Items

```CartItem``` implements ```ArrayAccess``` and uses the ```__set``` and ```__get``` magic methods to assign and access properties.

Added items will return a rowid.

```php
use jamesdb\Cart\Cart;
use jamesdb\Cart\CartItem;

$cart = new Cart(...);

/**
 * Assign properties via __set.
 */
$item1 = new CartItem();
$item1->id = 2731;
$item1->name = 'Product';
$item1->price = 1099;
$item1->quantity = 1;

$cart->add($item1);

/**
 * ----------
 */

/**
 * Assign properties via ArrayAccess.
 */
$item2 = new CartItem([
    'id' => 2731,
    'name' => 'Product',
    'price' => 1099,
    'quantity' => 1
]);

$cart->add($item2)
```

### Updating Items

You can either use the cart ```update``` method or access the properties directly on the ```CartItem```.

Attempting to update an item that doesn't exist in the cart will result in a ```CartItemUpdateException``` being thrown.

```php
$cart->update('rowid', ['name' => 'Renamed Product']);

/**
 * ----------
 */

$cartItem = $cart->find('rowid');
$cartItem->name = 'Renamed Product';
```

### Removing Items

Attempting to remove an item that doesn't exist in the cart will result in a ```CartItemRemoveException``` being thrown.

```php
$cart->remove('rowid');
```

### Clear the Cart

```php
$cart->clear();
```

### Accessing a specific Item

To access a specific item use the cart ```getItem``` method.

If an item can't be found the method will return ```null```.

```php
$cart->getItem('rowid');
```

### Accessing all Items

```php
$cart->getItems();
```

### Filtering Items

The cart can be filtered by any supplied key and value with the ```filter``` method.

```php
// Return all items with a quantity of 2.
$cart->filter('quantity', 2);

// Return all items with a price of 1000.
$cart->filter('price', 1000);
```
### Accessing Item counts

The ```getTotalUniqueItems``` method will return an item count excluding quantities.

```php
$cart->getTotalUniqueItems();
```

The ```getTotalItems``` method will return an item count including quantities.

```php
$cart->getTotalItems();
```

A convenience method ```isEmpty``` is built into the cart, this proxies through to the getTotalUniqueItems with a === 0 check.

```php
$cart->isEmpty();
```

### Accessing prices

Get the overall price including tax with the ```getTotalPrice``` method.

```php
$cart->getTotalPrice();
```

Occasionally dealing with tax isn't required, in this case you can use the ```getTotalPriceExcludingTax``` method.

```php
$cart->getTotalPriceExcludingTax();
```
Get the total cart tax.

```php
$cart->getTax();
```

## Events

[League\Event](http://event.thephpleague.com/2.0/) is built into the cart and provides a number of events that can be emitted, this allows you to easily hook into certain key points during the carts lifecycle.

You can subscribe to these events by attaching listeners to the cart via ```addEventListener```.

All events have built in methods to retrieve the item triggering the event and the cart itself.

```php
$cart->addEventListener('cart.add', function ($event) {
    $item = $event->getItem();
    $cart = $event->getCart();

    ...
});
```

### cart.add

```php
$cart->addEventListener('cart.add', function ($event) {});
```

### cart.update

```php
$cart->addEventListener('cart.update', function ($event) {});
```

### cart.remove

```php
$cart->addEventListener('cart.remove', function ($event) {});
```
