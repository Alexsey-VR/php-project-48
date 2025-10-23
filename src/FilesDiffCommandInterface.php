<?php

namespace Differ;

interface FilesDiffCommandInterface
{
    public function execute(CommandLineParserInterface $command): FilesDiffCommandInterface;
    public function getFile1Name(): string;
    public function getFile2Name(): string;

    /**
     * @return array<mixed,mixed>
     */
    public function getContent1Descriptor(): array;

    /**
     * @return array<mixed,mixed>
     */
    public function getContent2Descriptor(): array;

    /**
     * @return array<mixed,mixed>
     */
    public function getDifferenceDescriptor(): array;
}
