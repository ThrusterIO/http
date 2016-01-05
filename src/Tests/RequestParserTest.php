<?php

namespace Thruster\Component\Http\Tests;

use Psr\Http\Message\ServerRequestInterface;
use Thruster\Component\Http\Parser\ParserInterface;
use Thruster\Component\Http\RequestParser;

/**
 * Class RequestParserTest
 *
 * @package Thruster\Component\Http\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RequestParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Thruster\Component\Http\Exception\RequestEntityTooLargeException
     * @expectedExceptionMessage Request Entity Too Large
     */
    public function testHeadSizeLimit()
    {
        $options = [
            'max_head_size' => 1
        ];

        $requestParser = new RequestParser($options);

        $request = 'GET / HTTP/1.1' . "\r\n" .
            'Accept: text/demo' . "\r\n\r\n";

        $requestParser->onData($request);
    }

    /**
     * @expectedException \Thruster\Component\Http\Exception\RequestURITooLongException
     * @expectedExceptionMessage Request URI Too Long
     */
    public function testURISizeLimit()
    {
        $options = [
            'max_request_line' => 1
        ];

        $requestParser = new RequestParser($options);

        $request = 'GET / HTTP/1.1' . "\r\n" .
            'Accept: text/demo' . "\r\n\r\n";

        $requestParser->onData($request);
    }

    /**
     * @expectedException \Thruster\Component\Http\Exception\RequestHTTPVersionNotSupported
     * @expectedExceptionMessage HTTP Version Not Supported
     */
    public function testBadHTTPVersion()
    {
        $requestParser = new RequestParser();

        $request = 'GET / HTTP/1.2' . "\r\n" .
            'Accept: text/demo' . "\r\n\r\n";

        $requestParser->onData($request);
    }

    /**
     * @expectedException \Thruster\Component\Http\Exception\BadRequestException
     * @expectedExceptionMessage Bad Request
     */
    public function testBadRequestLine()
    {
        $requestParser = new RequestParser();

        $request = 'GET @(*^#$^$&#^ / HTTP 1.1' . "\r\n" .
            'Accept: text/demo' . "\r\n\r\n";

        $requestParser->onData($request);
    }

    /**
     * @expectedException \Thruster\Component\Http\Exception\RequestEntityTooLargeException
     * @expectedExceptionMessage Request Entity Too Large
     */
    public function testBodySizeLimit()
    {
        $options = [
            'max_body_size' => 1
        ];

        $requestParser = new RequestParser($options);

        $request = 'GET / HTTP/1.1' . "\r\n" .
            'Accept: text/demo' . "\r\n\r\n" .
            'Foo bar';

        $requestParser->onData($request);
    }
}
