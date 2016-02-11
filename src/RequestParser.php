<?php

namespace Thruster\Component\Http;

use Thruster\Component\EventEmitter\EventEmitterInterface;
use Thruster\Component\EventEmitter\EventEmitterTrait;
use Thruster\Component\Http\Exception\BadRequestException;
use Thruster\Component\Http\Exception\RequestEntityTooLargeException;
use Thruster\Component\Http\Exception\RequestHTTPVersionNotSupported;
use Thruster\Component\Http\Exception\RequestURITooLongException;
use Thruster\Component\HttpMessage\ServerRequest;

/**
 * Class RequestParser
 *
 * @package Thruster\Component\Http
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RequestParser implements EventEmitterInterface
{
    use EventEmitterTrait;

    const HEAD_SEPARATOR = "\r\n\r\n";

    /**
     * @var array
     */
    protected $options;

    /**
     * @var bool
     */
    protected $receivedHead;

    /**
     * @var string
     */
    protected $head;

    /**
     * @var resource
     */
    protected $body;

    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @var string
     */
    protected $protocolVersion;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $uri;

    public function __construct(array $options = [])
    {
        $options += [
            'max_head_size' => 8190,
            'max_request_line' => 8190,
            'max_body_size' => 10 * 1024 * 1024,
            'memory_limit' => 1 * 1024 * 1024,
            'supported_protocol_versions' => ['1.0' => true, '1.1' => true]
        ];

        $this->options = $options;
        $this->receivedHead = false;
        $this->head = '';
        $this->headers = [];

        $this->body = fopen('php://temp/maxmemory:' . $options['memory_limit'], 'r+b');

        if (false === $this->body) {
            throw new \RuntimeException('Could not open buffer for body');
        }
    }

    /**
     * @param $data

     * @throws BadRequestException
     * @throws RequestEntityTooLargeException
     * @throws RequestURITooLongException
     */
    public function onData($data)
    {
        if ($this->receivedHead) {
            fwrite($this->body, $data);

            $this->checkBodySize();
        } else {
            $this->head .= $data;

            if (false !== strpos($this->head, self::HEAD_SEPARATOR)) {
                list($head, $body) = explode(self::HEAD_SEPARATOR, $this->head, 2);

                fwrite($this->body, $body);
                $this->head = $head;

                $this->checkHeadSize();

                $this->parseHead();

                $this->checkProtocolVersion();

                $this->parseUri();

                $this->checkBodySize();
                $this->receivedHead = true;

                $this->emit('received_head', [$this->headers, $this->httpMethod, $this->uri, $this->protocolVersion]);
            }
        }

        if ($this->isRequestFinished()) {
            $this->emit('request', [$this->buildRequest()]);
        }
    }

    /**
     * @throws BadRequestException
     * @throws RequestURITooLongException
     */
    protected function parseHead()
    {
        $parsedRequestLine = false;
        foreach (explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $this->head)) as $line) {
            if (false === $parsedRequestLine) {
                if (strlen($line) > $this->options['max_request_line']) {
                    throw new RequestURITooLongException();
                }

                if (false == preg_match('/^[a-zA-Z]+\s+([a-zA-Z]+:\/\/|\/).*/', $line, $matches)) {
                    throw new BadRequestException();
                }

                $parts = explode(' ', $line, 3);
                $this->httpMethod = $parts[0];
                $this->protocolVersion = explode('/', $parts[2] ?? 'HTTP/1.1')[1];
                $this->uri = $parts[1];

                $parsedRequestLine = true;
                continue;
            }

            if (false !== strpos($line, ':')) {
                $parts = explode(':', $line, 2);
                $key = trim($parts[0]);
                $value = trim($parts[1] ?? '');

                $this->headers[$key][] = $value;
            }
        }
    }

    protected function parseUri()
    {
        $hostKey = array_filter(
            array_keys($this->headers),
            function ($key) {
                return 'host' === strtolower($key);
            }
        );

        if (!$hostKey) {
            return;
        }

        $host = $this->headers[reset($hostKey)][0];
        $scheme = ':443' === substr($host, -4) ? 'https' : 'http';

        $this->uri = $scheme . '://' . $host . '/' . ltrim($this->uri, '/');
    }

    protected function buildRequest()
    {
        rewind($this->body);

        return new ServerRequest(
            $this->httpMethod,
            $this->uri,
            $this->headers,
            $this->body,
            $this->protocolVersion
        );
    }

    protected function isRequestFinished() : bool
    {
        if (false === $this->receivedHead) {
            return false;
        }

        if (false === isset($this->headers['Content-Length'])) {
            return true;
        }

        $contentLength = max($this->headers['Content-Length']);
        if ($contentLength <= $this->getBodySize()) {
            return true;
        }

        return false;
    }

    /**
     * @throws RequestEntityTooLargeException
     */
    protected function checkBodySize()
    {
        if ($this->getBodySize() > $this->options['max_body_size']) {
            throw new RequestEntityTooLargeException();
        }
    }

    /**
     * @throws RequestEntityTooLargeException
     */
    protected function checkHeadSize()
    {
        if (strlen($this->head) > $this->options['max_head_size']) {
            throw new RequestEntityTooLargeException();
        }
    }

    /**
     * @throws RequestHTTPVersionNotSupported
     */
    protected function checkProtocolVersion()
    {
        if (false === isset($this->options['supported_protocol_versions'][$this->protocolVersion])) {
            throw new RequestHTTPVersionNotSupported();
        }
    }

    protected function getBodySize() : int
    {
        return fstat($this->body)['size'];
    }
}
