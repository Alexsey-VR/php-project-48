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
use Differ\Formatters;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;

#[CoversClass(CommandFactory::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(FileReader::class)]
#[CoversClass(DifferException::class)]
#[CoversClass(StylishCommand::class)]
#[CoversClass(PlainCommand::class)]
#[CoversClass(JSONCommand::class)]
#[CoversMethod(CommandFactory::class, 'createCommand')]
#[CoversClass(Formatters::class)]
class CommandFactoryTest extends TestCase
{
    private $commandFactory;

    public function setUp(): void
    {
        $this->commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader(),
            new Formatters()
        );
    }

    public function testCreateCommand()
    {
        $this->assertInstanceOf(CommandLineParser::class, $this->commandFactory->createCommand('parse'));

        $this->assertInstanceOf(FilesDiffCommand::class, $this->commandFactory->createCommand('difference'));

        $this->assertInstanceOf(StylishCommand::class, $this->commandFactory->createCommand('stylish'));

        $this->assertInstanceOf(PlainCommand::class, $this->commandFactory->createCommand('plain'));

        $this->assertInstanceOf(JSONCommand::class, $this->commandFactory->createCommand('json'));

        $this->assertInstanceOf(PlainCommand::class, $this->commandFactory->createCommand('plain'));

        $this->assertInstanceOf(DisplayCommand::class, $this->commandFactory->createCommand('show'));

        $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown command factory option\\n/");

        $this->commandFactory->createCommand('undefined');
    }
}
