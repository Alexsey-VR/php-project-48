<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\CoversClass;

use function Differ\Differ\genDiff;

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
            __DIR__ . "/../fixtures/filesRecursiveStylishDiffs.txt",
            $outputBuffer
        );
    }
}
