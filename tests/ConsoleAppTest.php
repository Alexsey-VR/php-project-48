<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(\Differ\ConsoleApp::class)]
#[CoversClass(\Differ\CommandFactory::class)]
#[CoversClass(\Differ\DisplayCommand::class)]
#[CoversClass(\Differ\FilesDiffCommand::class)]
#[CoversClass(\Differ\FileReader::class)]
#[CoversMethod(\Differ\ConsoleApp::class, 'run')]
#[CoversClass(\Differ\DocoptDouble::class)]
#[CoversClass(\Differ\CommandLineParser::class)]
#[CoversClass(\Differ\FileParser::class)]
#[CoversClass(\Differ\Formatters::class)]
#[CoversClass(\Differ\Formatters\StylishCommand::class)]
#[CoversClass(\Differ\Formatters\PlainCommand::class)]
class ConsoleAppTest extends TestCase
{
    public function testStylishConsoleAppRunning()
    {
        $commandFactory = new \Differ\CommandFactory(
            new \Differ\DocoptDouble(),
            new \Differ\FileReader(),
            new \Differ\Formatters()
        );

        $consoleApp = new \Differ\ConsoleApp($commandFactory);

        ob_start();
        $consoleApp->run();
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursiveStylishDiffs.txt",
            $outputBuffer
        );
    }

    public function testPlainConsoleAppRunning()
    {
        $commandFactory = new \Differ\CommandFactory(
            new \Differ\DocoptDouble("plain"),
            new \Differ\FileReader(),
            new \Differ\Formatters()
        );

        $consoleApp = new \Differ\ConsoleApp($commandFactory);

        ob_start();
        $consoleApp->run();
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursivePlainDiffs.txt",
            $outputBuffer
        );
    }
}
