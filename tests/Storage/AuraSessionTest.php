<?php

namespace jamesdb\Cart\Test\Storage;

use Aura\Session\CsrfTokenFactory;
use Aura\Session\Phpfunc;
use Aura\Session\Randval;
use Aura\Session\SegmentFactory;
use Aura\Session\Session;
use jamesdb\Cart\Storage\AuraSessionDriver;
use jamesdb\Cart\Test\Storage\Asset\FakePhpfunc;
use jamesdb\Cart\Test\Storage\Asset\FakeSessionHandler;

class AuraSessionTest extends \PHPUnit_Framework_TestCase
{
    protected $session;

    /**
     * Setup the session.
     */
    protected function setUp()
    {
        // There must be a better way? :/
        @session_start();

        $this->phpfunc = new FakePhpfunc;
        $handler = new FakeSessionHandler();
        session_set_save_handler(
            array($handler, 'open'),
            array($handler, 'close'),
            array($handler, 'read'),
            array($handler, 'write'),
            array($handler, 'destroy'),
            array($handler, 'gc')
        );

        $this->session = new Session(
            new SegmentFactory,
            new CsrfTokenFactory(new Randval(new Phpfunc)),
            $this->phpfunc,
            []
        );
    }

    /**
     * Test the storage stores data.
     */
    public function testAuraSessionStorageStores()
    {
        $data = 'bar';

        $storage = new AuraSessionDriver($this->session);
        $storage->store('foo', $data);

        $this->assertEquals($data, $storage->get('foo'));
    }

    /**
     * Test the storage clears data.
     */
    public function testAuraSessionStorageClears()
    {
        $data = 'bar';

        $storage = new AuraSessionDriver($this->session);
        $storage->store('foo', $data);

        $this->assertEquals($data, $storage->get('foo'));

        $storage->clear('foo');

        $this->assertEmpty($storage->get('foo'));
    }
}
