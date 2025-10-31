<?php

namespace Differ;

use Differ\CommandLineParserInterface as CLP;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\CommandInterface as CI;
use Differ\FormattersInterface as FI;

interface CommandInterface
{
    public function execute(CLP|FDCI|CI|FI $command): CI|FI;
}
