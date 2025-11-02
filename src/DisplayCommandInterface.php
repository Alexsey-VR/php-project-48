<?php

namespace Differ;

interface DisplayCommandInterface
{
    public function execute(FormattersInterface $command): DisplayCommandInterface;
    public function getFilesContent(): string;
    public function getFilesDiffs(): string;
}