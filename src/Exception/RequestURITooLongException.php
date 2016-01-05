<?php

namespace Thruster\Component\Http\Exception;

/**
 * Class RequestURITooLongException
 *
 * @package Thruster\Component\Http\Exception
 * @author Aurimas Niekis <aurimas@niekis.lt>
 */
class RequestURITooLongException extends RequestException
{
    public function __construct()
    {
        $message = 'Request URI Too Long';
        
        parent::__construct(414, $message);
    }
}
