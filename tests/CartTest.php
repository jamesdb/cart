<?php

namespace jamesdb\Cart\Test;

use jamesdb\Cart\Cart;
use jamesdb\Cart\CartItem;
use SebastianBergmann\Money\Currency;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup the Cart.
     */
    public function setUp()
    {
        $storageMock = $this->getMock('jamesdb\Cart\Storage\NativeSessionDriver');

        $identifierMock = $this->getMock('jamesdb\Cart\Identifier\IdentifierInterface');

        $identifierMock->expects($this->any())->method('get')->will($this->returnValue('cart'));

        $this->cart = new Cart($identifierMock, $storageMock);
    }

    /**
     * Tear down the Cart.
     */
    public function tearDown()
    {
        $this->cart->clear();
    }

    /**
     * Test to ensure a currency can be set.
     */
    public function testCartCurrencyCanBeSet()
    {
        $currency = new Currency('JPY');

        $this->cart->setCurrency($currency);

        $this->assertSame($this->cart->getCurrency(), $currency);
    }

    /**
     * Test the cart handles multiple instances.
     */
    public function testCartCanHandleMultipleInstances()
    {
        $identifierMock = $this->getMock('jamesdb\Cart\Identifier\IdentifierInterface');

        $storageMock = $this->getMock('jamesdb\Cart\Storage\NativeSessionDriver');

        $identifierMock->expects($this->at(0))->method('get')->will($this->returnValue('cart-1'));
        $identifierMock->expects($this->at(1))->method('get')->will($this->returnValue('cart-2'));

        $cart1 = new Cart($identifierMock, $storageMock);
        $cart2 = new Cart($identifierMock, $storageMock);

        $cart1->add(new CartItem([
            'id'    => 6,
            'name'  => 'Random T-Shirt',
            'price' => 1299
        ]));

        $cart1->add(new CartItem([
            'id'    => 23,
            'name'  => 'Random Hat',
            'price' => 799
        ]));

        $cart2->add(new CartItem([
            'id'    => 12,
            'name'  => 'Random Sunglasses',
            'price' => 599
        ]));

        $this->assertEquals($cart1->getTotalItems(), 2);
        $this->assertEquals($cart2->getTotalItems(), 1);

        $cart1->clear();
        $cart2->clear();

        $this->assertTrue($cart1->isEmpty());
        $this->assertTrue($cart2->isEmpty());
    }

    /**
     * Ensure generated row ids are excluding quantities.
     */
    public function testRowIdsIgnoringQuantity()
    {
        $item1 = new CartItem([
            'id'    => 32,
            'name'  => '100 Rubber Ducks',
            'price' => 4995
        ]);

        $item2 = new CartItem([
            'id'       => 32,
            'name'     => '100 Rubber Ducks',
            'price'    => 4995,
            'quantity' => 6
        ]);

        $this->assertSame($item1->getRowId(), $item2->getRowId());
        $this->assertSame($item1->rowid, $item2->rowid);
    }

    /**
     * Test that items can be added to the Cart.
     */
    public function testCartCanAddItem()
    {
        $this->assertEquals(0, $this->cart->getTotalItems());

        $item = new CartItem;
        $item->id = 1;
        $item->name = 'Nokia 3310';
        $item->price = 399;

        $this->cart->add($item);

        $this->assertEquals(1, $this->cart->getTotalItems());
        $this->assertFalse($this->cart->isEmpty());
    }

    /**
     * Test to ensure the cart is returning the amount of items we supply it with.
     */
    public function testCartReturnsItems()
    {
        $total = 10;

        for ($i = 1; $i <= $total; $i++) {
            $item = new CartItem([
                'id'    => $i,
                'name'  => uniqid($i),
                'price' => mt_rand(1, 50)
            ]);

            $this->cart->add($item);
        }

        $this->assertEquals(count($this->cart->getItems()), $total);
    }

    /**
     * Test that the cart can be filtered.
     */
    public function testCartCanBeFiltered()
    {
        $item1 = [
            'id'    => 7,
            'name'  => 'Pirate Eye Patch',
            'price' => 299
        ];

        $item2 = [
            'id'    => 4,
            'name'  => 'Black Pirate Hat',
            'price' => 499
        ];

        $this->cart->add(new CartItem($item1));
        $this->cart->add(new CartItem($item2));

        $filtered = $this->cart->filter('name', 'Pirate Eye Patch');

        $this->assertTrue(count($filtered) === 1);
        $this->assertSame($filtered[key($filtered)]['name'], $item1['name']);
    }

    /**
     * Test cart throws CartPropertyNotIntegerException.
     */
    public function testCartThrowsIntegerExceptionWhenInvalidPropertySupplied()
    {
        $this->setExpectedException('jamesdb\Cart\Exception\CartPropertyNotIntegerException');

        $item = new CartItem;
        $item->quantity = 'one';
    }

    /**
     * Test that cart handles item quantities correctly.
     */
    public function testCartIncrementsQuantityWhenMultipleItemsAdded()
    {
        $item1 = [
            'id'       => 5,
            'name'     => 'Teenage Mutant Ninja Turtles - T-Shirt',
            'price'    => 799,
            'quantity' => 2
        ];

        $item2 = [
            'id'       => 1,
            'name'     => 'Random Mug',
            'price'    => 499
        ];

        $this->cart->add(new CartItem($item1));

        $this->assertEquals(2, $this->cart->getTotalItems());

        $this->cart->add(new CartItem($item2));
        $this->cart->add(new CartItem($item2));

        $row = new CartItem($item2);

        $this->assertEquals(4, $this->cart->getTotalItems());
        $this->assertEquals(2, $this->cart->getTotalUniqueItems());
    }

    /**
     * Test that the cart can remove an item.
     */
    public function testCartRemovesItem()
    {
        $item = [
            'id'    => 5,
            'name'  => 'Will Smith - Big Willie Style',
            'price' => 299
        ];

        $row = $this->cart->add(new CartItem($item));

        $this->assertTrue($this->cart->remove($row));
    }

    /**
     * Test to ensure the CartItemRemoveException is being thrown.
     */
    public function testCartThrowsItemRemoveException()
    {
        $this->setExpectedException('jamesdb\Cart\Exception\CartItemRemoveException');

        $this->cart->remove('youwotm8');
    }

    /**
     * Test that an item can be updated.
     */
    public function testCartUpdatesItem()
    {
        $oldPrice = 499;
        $newPrice = 799;

        $item = [
            'id'      => 5,
            'name'    => 'Pocket Guide to Garden Sheds',
            'price'   => $oldPrice,
            'options' => ['format' => 'Paperback']
        ];

        $row = $this->cart->add(new CartItem($item));

        $this->cart->update($row, ['price' => $newPrice]);

        $updatedItem = $this->cart->getItem($row);

        $this->assertEquals($updatedItem->price, $newPrice);
    }

    /**
     * Test to ensure the CartItemUpdateException is being thrown.
     */
    public function testCartThrowsItemUpdateException()
    {
        $this->setExpectedException('jamesdb\Cart\Exception\CartItemUpdateException');

        $this->cart->update('youwotm8', ['name' => 'Whoops']);
    }

    /**
     * Test that the cart is returning the correct price including tax.
     */
    public function testCartPriceIncludingTax()
    {
        $item = [
            'id'    => 3,
            'name'  => 'Macbook Pro',
            'price' => 120000,
            'tax'   => 24000
        ];

        $this->cart->add(new CartItem($item));

        $this->assertEquals('1440', $this->cart->getTotalPrice());
        $this->assertEquals('240', $this->cart->getTotalTax());

        $this->cart->add(new CartItem($item));

        $this->assertEquals('2880', $this->cart->getTotalPrice());
        $this->assertEquals('480', $this->cart->getTotalTax());
    }

    /**
     * Test that the cart is returning the correct price excluding tax.
     */
    public function testCartPriceExcludingTax()
    {
        $item = [
            'id'       => 5,
            'name'     => 'Conkers Bad Fur Day',
            'price'    => 500,
            'quantity' => 4
        ];

        $this->cart->add(new CartItem($item));

        $this->assertEquals('20', $this->cart->getTotalPriceExcludingTax());
    }

    /**
     * Test the cart.add event is being triggered correctly.
     */
    public function testCartItemAddEvent()
    {
        $item = [
            'id'    => 7,
            'name'  => 'Large Gravy',
            'price' => 149
        ];

        $this->cart->addEventListener('cart.add', function($event) {
            $this->assertSame('cart.add', $event->getName());

            $this->assertInstanceOf('jamesdb\Cart\Cart', $event->getCart());

            $item = $event->getItem();

            $this->assertInstanceOf('jamesdb\Cart\CartItem', $item);
            $this->assertSame($item->id, 7);
            $this->assertSame($item->name, 'Large Gravy');
            $this->assertEquals($item->price, 149);
        });

        $this->cart->add(new CartItem($item));
    }

    /**
     * Test the cart.update event is being triggered correctly.
     */
    public function testCartItemUpdateEvent()
    {
        $oldPrice = 399;
        $newPrice = 499;

        $item = [
            'id'    => 11,
            'name'  => 'Tower Burger Meal',
            'price' => $oldPrice
        ];

        $this->cart->addEventListener('cart.update', function($event) use ($newPrice) {
            $this->assertSame('cart.update', $event->getName());

            $this->assertInstanceOf('jamesdb\Cart\Cart', $event->getCart());

            $item = $event->getItem();

            $this->assertInstanceOf('jamesdb\Cart\CartItem', $item);
            $this->assertSame($item->id, 11);
            $this->assertSame($item->name, 'Tower Burger Meal');
            $this->assertEquals($item->price, $newPrice);
        });

        $row = $this->cart->add(new CartItem($item));

        $this->assertEquals($this->cart->getItem($row)->price, $oldPrice);

        $this->cart->update($row, ['price' => $newPrice]);
    }

    /**
     * Test the cart.remove event is being triggered correctly.
     */
    public function testCartItemRemoveEvent()
    {
        $item = [
            'id'    => 15,
            'name'  => 'Fanta',
            'price' => 80
        ];

        $this->cart->addEventListener('cart.remove', function($event) {
            $this->assertSame('cart.remove', $event->getName());

            $this->assertInstanceOf('jamesdb\Cart\Cart', $event->getCart());

            $item = $event->getItem();

            $this->assertInstanceOf('jamesdb\Cart\CartItem', $item);
            $this->assertSame($item->id, 15);
            $this->assertSame($item->name, 'Fanta');
            $this->assertEquals($item->price, 80);
        });

        $row = $this->cart->add(new CartItem($item));

        $this->cart->remove($row);

        $this->assertTrue($this->cart->isEmpty());
    }

    /**
     * Test Cart array access.
     */
    public function testCartItemArrayAccess()
    {
        $item = new CartItem;
        $item->id = 1;
        $item->name = 'Daryl Dixon Walking Dead Funko Pop';
        $item->price = 999;

        $this->assertSame($item['id'], $item->id);
        $this->assertSame($item['name'], $item->name);
        $this->assertSame($item['price'], $item->price);

        $item = new CartItem;
        $item['id'] = 3;
        $item['name'] = 'Hershel Walking Dead Funko Pop';
        $item['price'] = 899;

        $this->assertSame($item['id'], $item->id);
        $this->assertSame($item['name'], $item->name);
        $this->assertSame($item['price'], $item->price);

        unset($item['name']);

        $this->assertFalse(isset($item['name']));
    }
}
