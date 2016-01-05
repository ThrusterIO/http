<?php

namespace Thruster\Component\Http;

use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class ServerRequest
 *
 * @package Thruster\Component\Http
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array|object|string
     */
    protected $parsedBody;

    /**
     * @var array
     */
    protected $serverParams;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $cookieParams;

    /**
     * @var array
     */
    protected $queryParams;

    /**
     * @var UploadedFileInterface[]
     */
    protected $uploadedFiles;

    /**
     * @param array                           $serverParams  Server parameters, typically from $_SERVER
     * @param array                           $uploadedFiles Upload file information, a tree of UploadedFiles
     * @param string                          $uri           URI for the request, if any.
     * @param string                          $method        HTTP method for the request, if any.
     * @param string|resource|StreamInterface $body          Message body, if any.
     * @param array                           $headers       Headers for the message, if any.
     * @param array                           $cookies       Cookies for the message, if any.
     * @param array                           $queryParams   Query params for the message, if any.
     * @param array|object                    $parsedBody    The deserialized body parameters, if any.
     * @param string                          $protocol      HTTP protocol version.
     *
     * @throws InvalidArgumentException for any invalid value.
     */
    public function __construct(
        array $serverParams = [],
        array $uploadedFiles = [],
        $uri = null,
        string $method = null,
        $body = null,
        array $headers = [],
        array $cookies = [],
        array $queryParams = [],
        $parsedBody = null,
        string $protocolVersion = '1.1'
    ) {
        $this->validateUploadedFiles($uploadedFiles);

        $this->attributes = [];

        $this->serverParams  = $serverParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->cookieParams  = $cookies;
        $this->queryParams   = $queryParams;
        $this->parsedBody    = $parsedBody;

        parent::__construct($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * @inheritDoc
     */
    public function getServerParams() : array
    {
        return $this->serverParams;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams() : array
    {
        return $this->cookieParams;
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams() : array
    {
        return $this->queryParams;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles() : array
    {
        return $this->uploadedFiles;
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->validateUploadedFiles($uploadedFiles);

        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data)
    {
        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value)
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name)
    {
        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }

    /**
     * Recursively validate the structure in an uploaded files array.
     *
     * @param array $uploadedFiles
     *
     * @throws InvalidArgumentException if any leaf is not an UploadedFileInterface instance.
     */
    protected function validateUploadedFiles(array $uploadedFiles)
    {
        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
                continue;
            }

            if (!$file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid leaf in uploaded files structure');
            }
        }
    }
}
