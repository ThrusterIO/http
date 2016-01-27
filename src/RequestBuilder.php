<?php

namespace Thruster\Component\Http;

use Psr\Http\Message\RequestInterface;
use Thruster\Component\Stream\Stream;

/**
 * Class RequestBuilder
 *
 * @package Thruster\Component\Http
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RequestBuilder
{
    /**
     * @var Stream
     */
    private $stream;

    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    public function generate(RequestInterface $request)
    {
        $msg = trim(
            $request->getMethod() . ' ' . $request->getRequestTarget() . ' HTTP/' . $request->getProtocolVersion()
        );

        if (false === $request->hasHeader('host')) {
            $msg .= "\r\nHost: " . $request->getUri()->getHost();
        }

        foreach ($request->getHeaders() as $name => $values) {
            $msg .= "\r\n" . $name . ': ' . implode(', ', $values);
        }

        $msg .= "\r\n\r\n";

        $this->stream->write($msg);

        $stream = $request->getBody()->detach();
        rewind($stream);

        $this->stream->pipeAll(new Stream($stream), $this->stream);
    }
}
