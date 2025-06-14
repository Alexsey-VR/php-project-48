<?php

namespace App;

interface FileReaderInterface
{
    public function readFile(string $filename): array | null;
}
