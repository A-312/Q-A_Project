<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\HttpFoundation\Tests\Session\Storage\Handler;

use Symfony\HttpFoundation\Session\Storage\Handler\WriteCheckSessionHandler;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class WriteCheckSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $wrappedSessionHandlerMock = $this->getMock('SessionHandlerInterface');
        $writeCheckSessionHandler = new WriteCheckSessionHandler($wrappedSessionHandlerMock);

        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('close')
            ->with()
            ->will($this->returnValue(true))
        ;

        $this->assertEquals(true, $writeCheckSessionHandler->close());
    }

    public function testWrite()
    {
        $wrappedSessionHandlerMock = $this->getMock('SessionHandlerInterface');
        $writeCheckSessionHandler = new WriteCheckSessionHandler($wrappedSessionHandlerMock);

        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('write')
            ->with('foo', 'bar')
            ->will($this->returnValue(true))
        ;

        $this->assertEquals(true, $writeCheckSessionHandler->write('foo', 'bar'));
    }

    public function testSkippedWrite()
    {
        $wrappedSessionHandlerMock = $this->getMock('SessionHandlerInterface');
        $writeCheckSessionHandler = new WriteCheckSessionHandler($wrappedSessionHandlerMock);

        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('read')
            ->with('foo')
            ->will($this->returnValue('bar'))
        ;

        $wrappedSessionHandlerMock
            ->expects($this->never())
            ->method('write')
        ;

        $this->assertEquals('bar', $writeCheckSessionHandler->read('foo'));
        $this->assertEquals(true, $writeCheckSessionHandler->write('foo', 'bar'));
    }

    public function testNonSkippedWrite()
    {
        $wrappedSessionHandlerMock = $this->getMock('SessionHandlerInterface');
        $writeCheckSessionHandler = new WriteCheckSessionHandler($wrappedSessionHandlerMock);

        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('read')
            ->with('foo')
            ->will($this->returnValue('bar'))
        ;

        $wrappedSessionHandlerMock
            ->expects($this->once())
            ->method('write')
            ->with('foo', 'baZZZ')
            ->will($this->returnValue(true))
        ;

        $this->assertEquals('bar', $writeCheckSessionHandler->read('foo'));
        $this->assertEquals(true, $writeCheckSessionHandler->write('foo', 'baZZZ'));
    }
}
