<?php

namespace Differ\Interfaces;

interface FileParserInterface
{
    /**
    * @return array<mixed,mixed>
    */
    public function execute(FileReaderInterface $fileReader, bool $isArray): array;
}
