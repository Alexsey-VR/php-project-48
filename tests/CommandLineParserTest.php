<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\CommandLineParser;

#[CoversClass(CommandLineParser::class)]
#[CoversMethod(CommandLineParser::class, 'setFileNames')]
#[CoversMethod(CommandLineParser::class, 'getFileNames')]
class CommandLineParserTest extends TestCase
{
    private $fixturesFileHandler;
    private $fixturesData;

    protected function setUp(): void
    {
        $this->fixturesFileHandler = fopen(__DIR__ . "/../fixtures/docopt.txt", "r");
        $docoptInput = [];
        while (($docoptInput[] = fgets($this->fixturesFileHandler, 4096)) !== false);
        $this->fixturesData = implode("\n", $docoptInput);
    }
    
    public function testFileNames()
    {
        $cmdLineParser = new CommandLineParser($this->fixturesData);

        $fileNames = [
            "FILE1" => __DIR__ . "/../file1.json",
            "FILE2" => __DIR__ . "/../file2.json"
        ];

        $this->assertInstanceOf(CommandLineParser::class, $cmdLineParser->setFileNames($fileNames));

        $this->assertEquals($fileNames, $cmdLineParser->setFileNames($fileNames)
                                                      ->getFileNames());
    }

    public function testExecute()
    {
        $cmdLineParser = new CommandLineParser($this->fixturesData);

        $stub = $this->createStub(CommandLineParser::class);

        $this->assertInstanceOf(CommandLineParser::class, $cmdLineParser->execute($stub));
    }

    protected function tearDown(): void
    {
        fclose($this->fixturesFileHandler);
    }
}