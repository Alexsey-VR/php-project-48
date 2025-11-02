<?php

namespace Differ;

use Differ\CommandLineParserInterface as CLP;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\CommandInterface as CI;
use Differ\FormattersInterface as FI;
use Differ\DisplayCommandInterface as DCI;

interface CommandInterface
{
    public function execute(CLP|FDCI|CI|FI|DCI $command): CI|FI;
}
