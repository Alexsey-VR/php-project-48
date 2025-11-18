<?php

namespace Differ\Exceptions;

class DifferException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
