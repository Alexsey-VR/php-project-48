<?php

namespace Differ\Exceptions;

use Exception;

class DifferException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
