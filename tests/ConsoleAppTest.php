<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\CommandFactory;
use Differ\DisplayCommand;
use Differ\FilesDiffCommand;
use Differ\FileReader;

#[CoversClass(ConsoleApp::class)]
#[CoversClass(CommandFactory::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(ConsoleApp::class, 'run')]
class ConsoleAppTest extends TestCase
{
    public function testConsoleAppRunning()
    {
        $outputString = "{\n" .
        "    hexlet.io\n" .
        "  - 50\n" .
        "  + 20\n" .
        "  - 123.234.53.22\n" .
        "  - \n" .
        "}\n";

        $commandLineParserStubToGetContent = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => [
                    'FILE1' => __DIR__ . "/../file1.json",
                    'FILE2' => __DIR__ . "/../file2.json"
                ]
            ]
        );
        $cmdLineParserStub = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'execute' => $commandLineParserStubToGetContent
            ]
        );

        $filesDiffCommand = new FilesDiffCommand();
        $displayCommand = new DisplayCommand();

        $commandFactoryMock = $this->createMock(CommandFactory::class);
        $commandFactoryMock->expects($this->exactly(3))
                           ->method('getCommand')
                           ->willReturnMap([
                                ['parse', $cmdLineParserStub],
                                ['difference', $filesDiffCommand],
                                ['show', $displayCommand]
                           ]);
        $fileReader = new FileReader();

        $consoleApp = new ConsoleApp($commandFactoryMock, $fileReader);
        $consoleApp->run();

        $this->expectOutputString($outputString);
    }
}