<?php

namespace App\Docker\Exception;

use App\Docker\Exception as BaseException;

/**
 * Docker\Exception\PortNotFoundException
 */
class PortNotFoundException extends BaseException
{
    /**
     * @param integer $port
     * @param string  $protocol
     */
    public function __construct($port, $protocol)
    {
        parent::__construct(sprintf('Port not found: %d/%s', $port, $protocol));
    }
}
