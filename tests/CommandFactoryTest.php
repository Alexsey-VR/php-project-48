<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\DisplayCommand;

#[CoversClass(CommandFactory::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(DisplayCommand::class)]
class CommandFactoryTest extends TestCase
{
    #[CoversFunction(CommandFactory::class, 'getCommand')]
    public function testGetCommand()
    {
        $fileHandler = fopen(__DIR__ . "/../fixtures/docopt.txt", 'r');
        $fileData = [];
        while (($fileData[] = fgets($fileHandler, 4096)) !== false);
        fclose($fileHandler);
        $docopt = implode("\n", $fileData);

        $commandFactory = new CommandFactory($docopt);

        // Test for CommandLineParse
        $this->assertInstanceOf(CommandLineParser::class, $commandFactory->getCommand('parse'));

        // Test for FileDiffCommand
        $this->assertInstanceOf(FilesDiffCommand::class, $commandFactory->getCommand('difference'));

        // Test for DisplayCommand
        $this->assertInstanceOf(DisplayCommand::class, $commandFactory->getCommand('show'));

        // Test for undefined command
        $this->assertNull($commandFactory->getCommand('undefined'));
    }
}
