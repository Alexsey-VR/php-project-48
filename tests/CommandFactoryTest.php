<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(\Differ\CommandFactory::class)]
#[CoversClass(\Differ\CommandLineParser::class)]
#[CoversClass(\Differ\FilesDiffCommand::class)]
#[CoversClass(\Differ\DisplayCommand::class)]
#[CoversClass(\Differ\DocoptDouble::class)]
#[CoversClass(\Differ\FileReader::class)]
#[CoversClass(\Differ\DifferException::class)]
#[CoversClass(\Differ\Formatters\StylishCommand::class)]
#[CoversClass(\Differ\Formatters\PlainCommand::class)]
#[CoversClass(\Differ\Formatters\JSONCommand::class)]
#[CoversMethod(\Differ\CommandFactory::class, 'createCommand')]
#[CoversClass(\Differ\Formatters::class)]
class CommandFactoryTest extends TestCase
{
    private $commandFactory;

    public function setUp(): void
    {
        $this->commandFactory = new \Differ\CommandFactory(
            new \Differ\DocoptDouble(),
            new \Differ\FileReader(),
            new \Differ\Formatters()
        );
    }

    public function testCreateCommand()
    {
        $this->assertInstanceOf(\Differ\CommandLineParser::class, $this->commandFactory->createCommand('parse'));

        $this->assertInstanceOf(\Differ\FilesDiffCommand::class, $this->commandFactory->createCommand('difference'));

        $this->assertInstanceOf(\Differ\Formatters\StylishCommand::class, $this->commandFactory->createCommand('stylish'));

        $this->assertInstanceOf(\Differ\Formatters\PlainCommand::class, $this->commandFactory->createCommand('plain'));

        $this->assertInstanceOf(\Differ\Formatters\JSONCommand::class, $this->commandFactory->createCommand('json'));

        $this->assertInstanceOf(\Differ\Formatters\PlainCommand::class, $this->commandFactory->createCommand('plain'));

        $this->assertInstanceOf(\Differ\DisplayCommand::class, $this->commandFactory->createCommand('show'));

        $this->expectException(\Differ\DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown command factory option\\n/");

        $this->commandFactory->createCommand('undefined');
    }
}
