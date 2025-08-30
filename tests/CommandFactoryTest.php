<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\DisplayCommand;
use Differ\FileReader;
use Differ\DocoptDouble;

#[CoversClass(CommandFactory::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(FileReader::class)]
class CommandFactoryTest extends TestCase
{
    #[CoversFunction(CommandFactory::class, 'getCommand')]
    public function testGetCommand()
    {
        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader()
        );

        // Test for CommandLineParser
        $this->assertInstanceOf(CommandLineParser::class, $commandFactory->getCommand('parse'));

        // Test for FilesDiffCommand
        $this->assertInstanceOf(FilesDiffCommand::class, $commandFactory->getCommand('difference'));

        // Test for DisplayCommand
        $this->assertInstanceOf(DisplayCommand::class, $commandFactory->getCommand('show'));

        // Test for undefined command
        $this->assertNull($commandFactory->getCommand('undefined'));
    }
}
