<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\CommandLineParser;
use Differ\DocoptDouble;

#[CoversClass(CommandLineParser::class)]
#[CoversMethod(CommandLineParser::class, 'setFileNames')]
#[CoversMethod(CommandLineParser::class, 'getFileNames')]
#[CoversClass(DocoptDouble::class)]
class CommandLineParserTest extends TestCase
{
    public function testFileNames()
    {
        $cmdLineParser = new CommandLineParser();

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
        $parser = new DocoptDouble();
        $cmdLineParser = new CommandLineParser($parser);

        ob_start();
        $cmdLineParser->execute();
        $outputBuffer = ob_get_clean();

        $this->assertEquals(
            "",
            $outputBuffer
        );
    }
        
}
