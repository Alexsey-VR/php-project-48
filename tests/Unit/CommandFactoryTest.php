<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\Parsers\DocoptDouble;
use Differ\Parsers\CommandLineParser;
use Differ\Parsers\FileParser;
use Differ\Factories\CommandFactory;
use Differ\Factories\Formatters;
use Differ\Displays\DisplayCommand;
use Differ\Differs\FilesDiffCommand;
use Differ\Readers\FileReader;
use Differ\Exceptions\DifferException;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;

#[CoversClass(CommandFactory::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(FileParser::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversClass(DifferException::class)]
#[CoversClass(DocoptDouble::class)]
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
        $this->assertInstanceOf(CommandLineParser::class, $this->commandFactory->createCommand('parseCMDLine'));

        $this->assertInstanceOf(FileParser::class, $this->commandFactory->createCommand("parseFile"));

        $this->assertInstanceOf(FilesDiffCommand::class, $this->commandFactory->createCommand('difference'));

        $this->assertInstanceOf(StylishCommand::class, $this->commandFactory->createCommand('stylish'));

        $this->assertInstanceOf(PlainCommand::class, $this->commandFactory->createCommand('plain'));

        $this->assertInstanceOf(JSONCommand::class, $this->commandFactory->createCommand('json'));

        $this->assertInstanceOf(DisplayCommand::class, $this->commandFactory->createCommand('show'));

        $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown command factory option\\n/");

        $this->commandFactory->createCommand('undefined');
    }
}
