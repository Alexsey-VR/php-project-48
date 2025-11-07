<?php

namespace Differ\Interfaces;

interface FileReaderInterface
{
    /**
    * @return array<mixed,mixed>
    */
    public function readFile(string $filename): array;
}
