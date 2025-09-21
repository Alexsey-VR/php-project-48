<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\CoversClass;
use function Differ\Differ\genDiff as genDiff;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\DisplayCommand;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlaneCommand;

#[CoversNothing]
class GenDiffUserTest extends TestCase
{
    public function testFilesDiffer()
    {
        $outputBuffer = genDiff(
            __DIR__ . "/../fixtures/file1.json",
            __DIR__ . "/../fixtures/file2.json",
        );

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursiveDiffs.txt",
            $outputBuffer
        );
    }

    public function testPlaneFilesDiffer()
    {
        $outputBuffer = genDiff(
            __DIR__ . "/../fixtures/file1.json",
            __DIR__ . "/../fixtures/file2.json",
            "plane"
        );

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursivePlaneDiffs.txt",
            $outputBuffer
        );
    }
}
