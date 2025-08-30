<?php

namespace Differ;

interface FileReaderInterface
{
    public function readFile(string $filename): ?array;
}
