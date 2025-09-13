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
class GenDiffUserTest extends TestCase
{
    public function testFilesDiffer()
    {
        ob_start();
        genDiff(
            __DIR__ . "/../fixtures/file1.json",
            __DIR__ . "/../fixtures/file2.json"
        );
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursiveDiffs.txt",
            $outputBuffer
        );
    }
}
