<?php

namespace Differ\Interfaces;

interface DisplayCommandInterface
{
    public function execute(FormattersInterface $command): DisplayCommandInterface;
    public function getFilesContent(): string;
    public function getFilesDiffs(): string;
    public function setFormatter(FormattersInterface $formatter): DisplayCommandInterface;
}
