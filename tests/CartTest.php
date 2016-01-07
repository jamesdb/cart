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

        $this->cart = new Cart('cart', $storageMock);
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
        $this->assertFalse($this->cart->getCurrency() === 'GBP');
    }

    /**
     * Test the cart handles multiple instances.
     */
    public function testCartCanHandleMultipleInstances()
    {
        $storageMock = $this->getMock('jamesdb\Cart\Storage\NativeSessionDriver');

        $cart1 = new Cart('cart-1', $storageMock);
        $cart2 = new Cart('cart-2', $storageMock);

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
    public function testRowIdCreationIgnoresQuantity()
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
        $this->assertFalse($this->cart->getTotalUniqueItems() === 1);
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

        $this->assertTrue($updatedItem->price === $newPrice);
        $this->assertFalse($updatedItem->price === $oldPrice);
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

        $this->assertEquals(20, $this->cart->getTotalPriceExcludingTax());
        $this->assertFalse($this->cart->getTotalPriceExcludingTax() === 0);
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
            $this->assertEquals($item->id, 7);
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

    /**
     * Ensure cart data can be restored from storage.
     */
    public function testCartCanBeRestored()
    {
        $data = serialize([
            'id' => 'cart-restore',
            'items' => [
                '59911bf22bb159432b8f8bed2f6d2657' => [
                    'id'   => '59911bf22bb159432b8f8bed2f6d2657',
                    'data' => [
                        'options'  => [],
                        'price'    => 3499,
                        'quantity' => 1,
                        'tax'      => 0,
                        'id'       => 23,
                        'name'     => 'Dank Souls 3'
                    ]
                ],
                '2477eb7e1f2f07a3eac161b93e96bbbe' => [
                    'id'   => '2477eb7e1f2f07a3eac161b93e96bbbe',
                    'data' => [
                        'options'  => [],
                        'price'    => 250000,
                        'quantity' => 1,
                        'tax'      => 0,
                        'id'       => 952,
                        'name'     => 'Sony PlayStation 4 Games Console'
                    ]
                ]
            ]
        ]);

        $storageMock = $this->getMock('jamesdb\Cart\Storage\StorageInterface');

        $storageMock->expects($this->any())
                    ->method('get')
                    ->with($this->equalTo('cart-restore'))
                    ->will($this->returnValue($data));

        $cartMock = $this->getMock('jamesdb\Cart\Cart', null, ['cart-restore', $storageMock]);

        $cartMock->restore();

        $this->assertEquals(2, $cartMock->getTotalUniqueItems());
        $this->assertSame($cartMock->getItem('59911bf22bb159432b8f8bed2f6d2657')->toArray(), unserialize($data)['items']['59911bf22bb159432b8f8bed2f6d2657']);
        $this->assertSame($cartMock->getItem('2477eb7e1f2f07a3eac161b93e96bbbe')->toArray(), unserialize($data)['items']['2477eb7e1f2f07a3eac161b93e96bbbe']);
    }

    /**
     * Ensure the restore method is correctly throwing a CartRestoreException.
     */
    public function testCartRestoreThrowsNotArrayException()
    {
        $this->setExpectedException('jamesdb\Cart\Exception\CartRestoreException', 'Data must be an array');

        $storageMock = $this->getMock('jamesdb\Cart\Storage\StorageInterface');

        $storageMock->expects($this->once())
                    ->method('get')
                    ->with($this->equalTo('cart-restore'))
                    ->will($this->returnValue(serialize('invalid data')));

        $cartMock = $this->getMock('jamesdb\Cart\Cart', null, ['cart-restore', $storageMock]);

        $cartMock->restore();

    }

    /**
     * Ensure restore throws CartRestoreException when no items key is supplied.
     */
    public function testCartRestoreThrowsStructureExceptionWhenNoItemsKey()
    {
        $this->setExpectedException('jamesdb\Cart\Exception\CartRestoreException', 'Storage data must have an id and items key');

        $storageMock = $this->getMock('jamesdb\Cart\Storage\StorageInterface');

        $storageMock->expects($this->once())
                    ->method('get')
                    ->with($this->equalTo('cart-restore'))
                    ->will($this->returnValue(serialize([
                        'id' => 'cart'
                    ])));

        $cartMock = $this->getMock('jamesdb\Cart\Cart', null, ['cart-restore', $storageMock]);

        $cartMock->restore();
    }

    /**
     * Ensure restore throws CartRestoreException when no id key is supplied.
     */
    public function testCartRestoreThrowsStructureExceptionWhenNoIdKey()
    {
        $this->setExpectedException('jamesdb\Cart\Exception\CartRestoreException', 'Storage data must have an id and items key');

        $storageMock = $this->getMock('jamesdb\Cart\Storage\StorageInterface');

        $storageMock->expects($this->once())
                    ->method('get')
                    ->with($this->equalTo('cart-restore'))
                    ->will($this->returnValue(serialize([
                        'items' => []
                    ])));

        $cartMock = $this->getMock('jamesdb\Cart\Cart', null, ['cart-restore', $storageMock]);

        $cartMock->restore();
    }

    /**
     * Ensure restore throws an exception when the returned id isn't a string.
     */
    public function testRestoreThrowsDataTypeExceptionWhenInvalidIdSupplied()
    {
        $this->setExpectedException('jamesdb\Cart\Exception\CartRestoreException', 'Invalid storage data type, ensure id is a string');

        $storageMock = $this->getMock('jamesdb\Cart\Storage\StorageInterface');

        $storageMock->expects($this->once())
                    ->method('get')
                    ->with($this->equalTo('cart-restore'))
                    ->will($this->returnValue(serialize([
                        'id' => [],
                        'items' => []
                    ])));

        $cartMock = $this->getMock('jamesdb\Cart\Cart', null, ['cart-restore', $storageMock]);

        $cartMock->restore();
    }

    /**
     * Ensure restore throws an exception when the returned items isn't an array.
     */
    public function testRestoreThrowsDataTypeExceptionWhenInvalidItemsSupplied()
    {
        $this->setExpectedException('jamesdb\Cart\Exception\CartRestoreException', 'Invalid storage data type, ensure items are an array');

        $storageMock = $this->getMock('jamesdb\Cart\Storage\StorageInterface');

        $storageMock->expects($this->once())
                    ->method('get')
                    ->with($this->equalTo('cart-restore'))
                    ->will($this->returnValue(serialize([
                        'id' => 'cart',
                        'items' => 'invalid'
                    ])));

        $cartMock = $this->getMock('jamesdb\Cart\Cart', null, ['cart-restore', $storageMock]);

        $cartMock->restore();
    }
}
