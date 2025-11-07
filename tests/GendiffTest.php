<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

use function Differ\runGendiff;

#[CoversNothing]
class GendiffTest extends TestCase
{
    public function testRunGendiff()
    {
        $commandFactory = new \Differ\CommandFactory(
            new \Differ\DocoptDouble(),
            new \Differ\FileReader(),
            new \Differ\Formatters()
        );

        ob_start();
        runGendiff($commandFactory);
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursiveStylishDiffs.txt",
            $outputBuffer
        );
    }

    public function testRunPlainGendiff()
    {
        $commandFactory = new \Differ\CommandFactory(
            new \Differ\DocoptDouble("plain"),
            new \Differ\FileReader(),
            new \Differ\Formatters()
        );

        ob_start();
        runGendiff($commandFactory);
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursivePlainDiffs.txt",
            $outputBuffer
        );
    }
}
