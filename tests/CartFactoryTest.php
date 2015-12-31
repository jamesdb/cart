<?php

namespace jamesdb\Cart\Test;

use jamesdb\Cart\CartFactory;

class CartFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Assert that the CartFactory is returning an instance of the cart.
     */
    public function testCartFactoryCreatesNewInstance()
    {
        $factory = new CartFactory;

        $cart = $factory->newInstance('test', 'NativeSessionDriver');

        $this->assertInstanceOf('jamesdb\Cart\Cart', $cart);
        $this->assertInstanceOf('SebastianBergmann\Money\Currency', $cart->getCurrency());
        $this->assertEquals('GBP', $cart->getCurrency());
    }

    /**
     * Assert that the CartFactory is returning a correctly configured currency object.
     */
    public function testCartFactoryCreatesCartInstanceWithCorrectCurrency()
    {
        $factory = new CartFactory;

        $cart = $factory->newInstance('test', 'NativeSessionDriver', 'USD');

        $this->assertInstanceOf('jamesdb\Cart\Cart', $cart);
        $this->assertInstanceOf('SebastianBergmann\Money\Currency', $cart->getCurrency());
        $this->assertEquals('USD', $cart->getCurrency());
    }
}
