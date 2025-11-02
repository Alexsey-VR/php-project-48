<?php

namespace Differ;

use Differ\CommandLineParserInterface as CLPI;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\CommandInterface as CI;
use Differ\FormattersInterface as FI;
use Differ\DisplayCommandInterface as DCI;

interface CommandFactoryInterface
{
    public function createCommand(string $commandType): CLPI|FDCI|CI|FI|DCI;
}
