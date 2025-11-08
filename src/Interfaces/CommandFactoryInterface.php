<?php

namespace Differ\Interfaces;

use Differ\Interfaces\CommandLineParserInterface as CLPI;
use Differ\Interfaces\FileParserInterface as FPI;
use Differ\Interfaces\FilesDiffCommandInterface as FDCI;
use Differ\Interfaces\FormattersInterface as FI;
use Differ\Interfaces\DisplayCommandInterface as DCI;

interface CommandFactoryInterface
{
    public function createCommand(string $commandType): CLPI | FDCI | FI | DCI | FPI;
}
