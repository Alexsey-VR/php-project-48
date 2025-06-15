<?php

namespace App;

interface CommandInterface
{
    public function execute(object $data): object | null;
}
