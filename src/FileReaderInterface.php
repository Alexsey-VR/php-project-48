<?php

namespace Differ;

interface FileReaderInterface
{
    /**
    * @return array<mixed,mixed>
    */
    public function readFile(string $filename): array;
}
