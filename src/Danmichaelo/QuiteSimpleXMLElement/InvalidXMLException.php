<?php

namespace Danmichaelo\QuiteSimpleXMLElement;

use Exception;
use RuntimeException;

class InvalidXMLException extends RuntimeException
{
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}