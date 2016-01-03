<?php

namespace jamesdb\Cart\Test\Storage;

use jamesdb\Cart\Storage\NativeSessionDriver;

class NativeSessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the storage stores data.
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
