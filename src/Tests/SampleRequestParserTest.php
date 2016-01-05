<?php

namespace Thruster\Component\Http\Tests;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Thruster\Component\Http\RequestParser;

/**
 * Class SampleRequestParserTest
 *
 * @package Thruster\Component\Http\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class SampleRequestParserTest extends \PHPUnit_Framework_TestCase
{

    public function dataRequest()
    {
        yield ['1_simple_request'];
        yield ['2_normal_browser_request'];
        yield ['3_normal_browser_request_query_params'];
        yield ['4_json_post_request'];
    }

    /**
     * @dataProvider  dataRequest
     */
    public function testRequest($file)
    {
        $requestParser = new RequestParser();
        $requestParser->on('request', function ($request) use ($file) {
            $this->validateRequestObject($request, $file);
        });

        $this->feedRandomSideDataChunks(__DIR__ . '/Fixtures/' . $file . '.txt', $requestParser);
    }

    /**
     * @param string        $data
     * @param RequestParser $object
     */
    protected function feedRandomSideDataChunks(string $filename, RequestParser $object)
    {
        $fp = fopen($filename, 'r');
        while ($line = fread($fp, '10')) {
            $object->onData($line);
        }
    }

    protected function validateRequestObject(ServerRequestInterface $request, string $filename)
    {
        $path = __DIR__ . '/Fixtures/' . $filename . '.json';
        $json = json_decode(file_get_contents($path), true);

        $this->assertSame($json['request_method'], $request->getMethod());
        $this->assertSame($json['protocol_version'], $request->getProtocolVersion());
        $this->assertEquals($json['headers'], $request->getHeaders());

        $uri = $request->getUri();

        $this->assertSame($json['uri']['scheme'], $uri->getScheme());
        $this->assertSame($json['uri']['user_info'], $uri->getUserInfo());
        $this->assertSame($json['uri']['host'], $uri->getHost());
        $this->assertSame($json['uri']['port'], $uri->getPort());
        $this->assertSame($json['uri']['path'], $uri->getPath());
        $this->assertSame($json['uri']['query'], $uri->getQuery());
        $this->assertSame($json['uri']['fragment'], $uri->getFragment());

        $this->assertEquals($json['query_params'], $request->getQueryParams());
        $this->assertEquals($json['cookie_params'], $request->getCookieParams());
        $this->assertEquals($json['server_params'], $request->getServerParams());

        if (isset($json['body'])) {
            $fp = fopen(__DIR__ . '/Fixtures/' . $json['body'], 'r');
            $this->assertSameStream($fp, $request->getBody()->detach());
        }

        if (isset($json['parsed_body'])) {
            $this->assertEquals($json['parsed_body'], $request->getParsedBody());
        }

        $uploadedFiles = $request->getUploadedFiles();
        foreach ($uploadedFiles as $index => $uploadedFile) {
            $this->assertSame($json['uploaded_files'][$index]['error'], $uploadedFile->getError());
            $this->assertSame($json['uploaded_files'][$index]['filename'], $uploadedFile->getClientFilename());
            $this->assertSame($json['uploaded_files'][$index]['mediatype'], $uploadedFile->getClientMediaType());
            $this->assertSame($json['uploaded_files'][$index]['size'], $uploadedFile->getSize());

            $expectedStream = fopen(__DIR__ . '/Fixtures/' . $json['uploaded_files'][$index]['file'], 'r');
            $givenStream = $uploadedFile->getStream()->detach();

            $this->assertSameStream($expectedStream, $givenStream);
        }

    }

    protected function assertSameStream($expected, $given)
    {
        rewind($expected);
        rewind($given);

        while ($expectedLine = fread($expected, 1024)) {
            $givenLine = fread($given, 1024);

            $this->assertSame($expectedLine, $givenLine);
        }
    }
}
