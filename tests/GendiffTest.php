<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use function Differ\runGendiff;

#[CoversNothing]
class GendiffTest extends TestCase
{
    public function testRunGendiff()
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

        $this->expectOutputString(
            $outputString);        

        runGendiff($commandFactoryMock);
    }
}