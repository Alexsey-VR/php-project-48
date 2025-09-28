<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use function Differ\runGendiff;
use Differ\DocoptDouble;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;

#[CoversNothing]
class GendiffTest extends TestCase
{
    public function testRunGendiff()
    {
        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader()
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
        $commandFactory = new CommandFactory(
            new DocoptDouble("plain"),
            new FileReader()
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
