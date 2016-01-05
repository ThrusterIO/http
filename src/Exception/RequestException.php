<?php

namespace Thruster\Component\Http\Exception;

/**
 * Class RequestException
 *
 * @package Thruster\Component\Http\Exception
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RequestException extends \Exception
{
    /**
     * @var int
     */
    protected $responseCode;

    /**
     * @param int    $code
     * @param string $message
     */
    public function __construct(int $code, string $message)
    {
        $this->responseCode = $code;

        parent::__construct($message, $code);
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }
}
