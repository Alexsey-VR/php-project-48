<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use function Differ\runGendiff;
use Differ\DocoptDouble;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlaneCommand;

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
            __DIR__ . "/../fixtures/filesRecursiveDiffs.txt",
            $outputBuffer
        );
    }

    public function testRunPlaneGendiff()
    {
        $commandFactory = new CommandFactory(
            new DocoptDouble("plane"),
            new FileReader()
        );

        ob_start();
        runGendiff($commandFactory);
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursivePlaneDiffs.txt",
            $outputBuffer
        );
    }
}
