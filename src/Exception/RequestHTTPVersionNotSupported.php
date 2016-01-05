<?php

namespace Thruster\Component\Http\Exception;

/**
 * Class RequestHTTPVersionNotSupported
 *
 * @package Thruster\Component\Http\Exception
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RequestHTTPVersionNotSupported extends RequestException
{
    public function __construct()
    {
        $message = 'HTTP Version Not Supported';

        parent::__construct(505, $message);
    }
}
