<?php

namespace Thruster\Component\Http\Exception;

/**
 * Class RequestEntityTooLargeException
 *
 * @package Thruster\Component\Http\Exception
 * @author Aurimas Niekis <aurimas@niekis.lt>
 */
class RequestEntityTooLargeException extends RequestException
{
    public function __construct()
    {
        $message = 'Request Entity Too Large';

        parent::__construct(413, $message);
    }
}
