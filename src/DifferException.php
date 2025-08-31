<?php

namespace Differ;

class DifferException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
