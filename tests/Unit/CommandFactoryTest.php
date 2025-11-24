<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\Tests\Fixtures\DocoptDouble;
use Differ\Factories\CommandFactory;
use Differ\Factories\Formatters;
use Differ\Displays\DisplayCommand;
use Differ\Differs\FilesDiffCommand;
use Differ\Readers\FileReader;
use Differ\Exceptions\DifferException;

#[CoversClass(CommandFactory::class)]
#[CoversClass(\Differ\Parsers\CommandLineParser::class)]
#[CoversClass(\Differ\Parsers\FileParser::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversClass(DifferException::class)]
#[CoversClass(\Differ\Formatters\StylishCommand::class)]
#[CoversClass(\Differ\Formatters\PlainCommand::class)]
#[CoversClass(\Differ\Formatters\JSONCommand::class)]
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
        $this->assertInstanceOf(\Differ\Parsers\CommandLineParser::class, $this->commandFactory->createCommand('parseCMDLine'));

        $this->assertInstanceOf(\Differ\Parsers\FileParser::class, $this->commandFactory->createCommand("parseFile"));

        $this->assertInstanceOf(FilesDiffCommand::class, $this->commandFactory->createCommand('difference'));

        $this->assertInstanceOf(\Differ\Formatters\StylishCommand::class, $this->commandFactory->createCommand('stylish'));

        $this->assertInstanceOf(\Differ\Formatters\PlainCommand::class, $this->commandFactory->createCommand('plain'));

        $this->assertInstanceOf(\Differ\Formatters\JSONCommand::class, $this->commandFactory->createCommand('json'));

        $this->assertInstanceOf(DisplayCommand::class, $this->commandFactory->createCommand('show'));

        $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown command factory option\\n/");

        $this->commandFactory->createCommand('undefined');
    }
}
