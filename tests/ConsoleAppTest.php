<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\CommandFactory;
use Differ\DisplayCommand;
use Differ\FilesDiffCommand;
use Differ\Formatters;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\FileReader;

#[CoversClass(ConsoleApp::class)]
#[CoversClass(CommandFactory::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(ConsoleApp::class, 'run')]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(Formatters::class)]
#[CoversClass(StylishCommand::class)]
#[CoversClass(PlainCommand::class)]
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

        $consoleApp = new ConsoleApp($commandFactory);

        ob_start();
        $consoleApp->run();
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursivePlainDiffs.txt",
            $outputBuffer
        );
    }
}
