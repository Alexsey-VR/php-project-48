<?php

namespace Differ;

use Differ\CommandLineParserInterface as CLP;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\CommandInterface as CI;

interface CommandInterface
{
    public function execute(CLP|FDCI|CI $command): CommandInterface;
}
