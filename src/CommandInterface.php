<?php

namespace Differ;

interface CommandInterface
{
    public function execute(object $data): object | null;
}
