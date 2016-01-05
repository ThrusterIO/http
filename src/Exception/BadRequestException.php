<?php

namespace Thruster\Component\Http\Exception;

/**
 * Class BadRequestException
 *
 * @package Thruster\Component\Http\Exception
 * @author Aurimas Niekis <aurimas@niekis.lt>
 */
class BadRequestException extends RequestException
{
    public function __construct()
    {
        $message = 'Bad Request';

        parent::__construct(400, $message);
    }
}
