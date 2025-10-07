<?php

namespace Differ;

interface DisplayCommandInterface
{
    public function execute(FormattersInterface $command): FormattersInterface;
    public function getFilesContent(): string;
    public function getFilesDiffs(): string;
}
