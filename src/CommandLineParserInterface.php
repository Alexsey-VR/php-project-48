<?php

namespace Differ;

interface CommandLineParserInterface
{
    public function execute(CommandLineParserInterface $command): CommandLineParserInterface;
    /**
     * @param array<string,string> $fileNames
     */
    public function setFileNames(array $fileNames): CommandLineParserInterface;
    /**
     * @return array<string,string>
     */
    public function getFileNames(): array;
    public function setFormat(string $format): CommandLineParserInterface;
    public function getFormat(): string;
}