<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\CommandLineParser;
use Differ\DocoptDouble;

#[CoversClass(CommandLineParser::class)]
#[CoversMethod(CommandLineParser::class, 'setFileNames')]
#[CoversMethod(CommandLineParser::class, 'getFileNames')]
#[CoversClass(DocoptDouble::class)]
class CommandLineParserTest extends TestCase
{
    public static function getFiles(): array
    {
        $fileNamesData = [
            /*
            [
                "FILE1" => __DIR__ . "/../fixtures/file1.json",
                "FILE2" => __DIR__ . "/../fixtures/file2.json"
            ],*/
            [
                "fileNames" => [
                    "FILE1" => __DIR__ . "/../fixtures/file1Entry.json",
                    "FILE2" => __DIR__ . "/../fixtures/file2Entry.json"
                ]
            ],
            [
                "fileNames" => [
                    "FILE1" => __DIR__ . "/../fixtures/file1Entry.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2Entry.yaml"
                ]
            ]
        ];

        return $fileNamesData;
    }

    #[DataProvider('getFiles')]
    public function testFileNames($fileNames)
    {
        $cmdLineParser = new CommandLineParser();

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
