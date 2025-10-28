<?php

namespace Differ;

interface FileReaderInterface
{
    /**
    * @return array<string,string>
    */
    public function readFile(string $filename): array;
}
