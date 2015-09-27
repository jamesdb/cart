# Shopping Cart

A framework agnostic shopping cart package.

## Notes

### Money Value Objects

This package uses Sebastian Bergmann's [money library](https://github.com/sebastianbergmann/money) (an implementation of Martin Fowler's money pattern) to represent monetary values. It avoids using floats due to them being ill-suited for monetary values. More information can be found in the links below:

[http://martinfowler.com/eaaCatalog/money.html](http://martinfowler.com/eaaCatalog/money.html)
[http://culttt.com/2014/05/28/handle-money-currency-web-applications/](http://culttt.com/2014/05/28/handle-money-currency-web-applications/)

Due to this when dealing with monetary values they will need to be represented as integers.

An example of this can be found below.

```php
use jamesdb\Cart\CartItem;
use SebastianBergmann\Money\Money;

$item = new CartItem([
    ...
    'price' => 1099, // Instead of £10.99 or $10.99 etc.
    'price' => Money::fromString('10.99', $cart->getCurrency()) // Alternatively you could convert the floats to integers.
    ...
]);
```

## Setting Up

To setup a cart instance you need to pass an id and a storage implementation to the cart constructor.

The currency defaults to 'GBP', if you want to change this you will need to pass your currency of choice into the ```setCurrency``` method as shown below.

```php
use jamesdb\Cart\Cart;
use jamesdb\Cart\Storage\NativeSessionDriver;
use SebastianBergmann\Money\Currency;

$cart = new Cart('cart', new NativeSessionDriver);
$cart->setCurrency(new Currency('GBP'));
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

To access a specific item use the cart ```item``` method.

If an item can't be found the method will return ```null```.

```php
$cart->item('rowid');
```

### Accessing all Items

```php
$cart->items();
```

### Filtering Items

The cart can be filtered by any supplied key and value with the ```filter``` method.

```php
// Return all items with a quantity of 2.
$cart->filter('quantity', '2');

// Return all items with a price of 10.
$cart->filter('price', 10);
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

For your convenience a method ```isEmpty``` is built into the cart, this proxies through to the getTotalUniqueItems with a === 0 check.

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
