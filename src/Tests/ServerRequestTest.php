<?php

namespace Thruster\Component\Http\Tests;

use Thruster\Component\Http\ServerRequest;

/**
 * Class ServerRequestTest
 *
 * @package Thruster\Component\Http\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class ServerRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testAttributes()
    {
        $serverRequest = new ServerRequest([], [], '/');

        $this->assertSame('foobar', $serverRequest->getAttribute('foo', 'foobar'));
        $this->assertCount(0, $serverRequest->getAttributes());

        $serverRequest = $serverRequest->withAttribute('foo', 'bar');

        $this->assertCount(1, $serverRequest->getAttributes());
        $this->assertEquals(['foo' => 'bar'], $serverRequest->getAttributes());
        $this->assertSame('bar', $serverRequest->getAttribute('foo'));

        $serverRequest = $serverRequest->withoutAttribute('foo');

        $this->assertCount(0, $serverRequest->getAttributes());
        $this->assertSame('foo', $serverRequest->getAttribute('foo', 'foo'));
    }
}
