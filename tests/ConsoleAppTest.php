<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\Parsers\DocoptDouble;
use Differ\Factories\CommandFactory;
use Differ\Factories\Formatters;
use Differ\Displays\DisplayCommand;
use Differ\Differs\FilesDiffCommand;
use Differ\Readers\FileReader;
use Differ\ConsoleApp;

#[CoversClass(\Differ\ConsoleApp::class)]
#[CoversClass(CommandFactory::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(ConsoleApp::class, 'run')]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(\Differ\Parsers\CommandLineParser::class)]
#[CoversClass(\Differ\Parsers\FileParser::class)]
#[CoversClass(Formatters::class)]
#[CoversClass(\Differ\Formatters\StylishCommand::class)]
#[CoversClass(\Differ\Formatters\PlainCommand::class)]
#[CoversClass(DisplayCommand::class)]
class ConsoleAppTest extends TestCase
{
    public function testStylishConsoleAppRunning()
    {
        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader(),
            new Formatters()
        );

        $consoleApp = new ConsoleApp($commandFactory);

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
        $commandFactory = new CommandFactory(
            new DocoptDouble("plain"),
            new FileReader(),
            new Formatters()
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
