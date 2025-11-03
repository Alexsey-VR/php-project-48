<?php

namespace Differ;

use Differ\DisplayCommandInterface as DCI;
use Differ\FormattersInterface as FI;

interface DisplayCommandInterface
{
    public function execute(FormattersInterface $command): DCI;
    public function getFilesContent(): string;
    public function getFilesDiffs(): string;
    public function setFormatter(FI $formatter): DCI;
}
