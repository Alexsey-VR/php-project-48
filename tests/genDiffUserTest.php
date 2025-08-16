<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use function Differ\Differ\genDiff as genDiff;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\DisplayCommand;

#[CoversNothing]
class genDiffUserTest extends TestCase
{
    function testFilesDiffer()
    {
        $outputString = "{\n" .
        "    hexlet.io\n" .
        "  - 50\n" .
        "  + 20\n" .
        "  - 123.234.53.22\n" .
        "  - \n" .
        "}\n";

        $this->expectOutputString($outputString);

        genDiff(__DIR__ . "/../file1.json", __DIR__ . "/../file2.json");
    }
}