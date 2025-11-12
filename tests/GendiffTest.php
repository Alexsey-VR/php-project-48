<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Differ\Parsers\DocoptDouble;
use Differ\Factories\CommandFactory;
use Differ\Factories\Formatters;

use function Differ\runGendiff;

#[CoversNothing]
class GendiffTest extends TestCase
{
    public function testRunGendiff()
    {
        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new \Differ\FileReader(),
            new Formatters()
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
            new \Differ\FileReader(),
            new Formatters()
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
