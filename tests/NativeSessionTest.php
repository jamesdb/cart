<?php

namespace jamesdb\Cart\Test;

use jamesdb\Cart\Storage\NativeSessionDriver;

class NativeSessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup the Storage mock.
     *
     * @return void
     */
    public function setUp()
    {
        $this->storage = $this->getMock('jamesdb\Cart\Storage\NativeSessionDriver');
    }

    /**
     * Test that storage stores data.
     *
     * @return void
     */
    public function testMockCartStorageStores()
    {
        $data = 'bar';

        $this->storage->expects($this->once())
                      ->method('get')
                      ->with($this->equalTo('foo'))
                      ->will($this->returnValue($data));

        $this->assertSame($this->storage->get('foo'), $data);
    }

    /**
     * Test the storage stores data.
     *
     * @return void
     */
    public function testStorageStores()
    {
        $data = 'bar';

        $storage = new NativeSessionDriver();
        $storage->store('foo', $data);

        $this->assertEquals($data, $_SESSION['foo']);
    }

    /**
     * Test the storage clears data.
     *
     * @return void
     */
    public function testStorageClears()
    {
        $data = 'bar';

        $storage = new NativeSessionDriver();
        $storage->store('foo', $data);

        $this->assertEquals($data, $_SESSION['foo']);

        $storage->clear('foo');

        $this->assertEmpty($storage->get('foo'));
    }
}
