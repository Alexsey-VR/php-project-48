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
use Differ\DifferException;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;

#[CoversClass(CommandFactory::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(FileReader::class)]
#[CoversClass(DifferException::class)]
#[CoversClass(StylishCommand::class)]
#[CoversClass(PlainCommand::class)]
#[CoversMethod(CommandFactory::class, 'getCommand')]
class CommandFactoryTest extends TestCase
{
    private $commandFactory;

    public function setUp(): void
    {
        $this->commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader()
        );
    }


    public function testGetCommand()
    {
        // Test for CommandLineParser
        $this->assertInstanceOf(CommandLineParser::class, $this->commandFactory->getCommand('parse'));

        // Test for FilesDiffCommand
        $this->assertInstanceOf(FilesDiffCommand::class, $this->commandFactory->getCommand('difference'));
/*
        // Test for FilesDiffCommand
        $this->assertInstanceOf(StylishCommand::class, $this->commandFactory->getCommand('stylish'));

        // Test for FilesDiffCommand
        $this->assertInstanceOf(PlainCommand::class, $this->commandFactory->getCommand('plain'));
*/
        // Test for DisplayCommand
        $this->assertInstanceOf(DisplayCommand::class, $this->commandFactory->getCommand('show'));

        // Test for undefined command
        $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown command factory option\\n/");

        $this->commandFactory->getCommand('undefined');
    }
}
