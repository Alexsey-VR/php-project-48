<?php

namespace Differ;

interface CommandInterface
{
    public function execute(array $data): ?array;
}
