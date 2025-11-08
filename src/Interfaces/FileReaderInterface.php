<?php

namespace Differ\Interfaces;

interface FileReaderInterface
{
    public function readFile(string $filename): FileReaderInterface;
    public function getName(): string;
    public function getFormat(): string;
    public function getContent(): string;
}
