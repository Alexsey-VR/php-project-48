<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(\Differ\CommandLineParser::class)]
#[CoversMethod(\Differ\CommandLineParser::class, 'setFileNames')]
#[CoversMethod(\Differ\CommandLineParser::class, 'getFileNames')]
#[CoversClass(\Differ\DocoptDouble::class)]
class CommandLineParserTest extends TestCase
{
    public static function getFiles(): array
    {
        return [
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
    }

    #[DataProvider('getFiles')]
    public function testFileNames($fileNames)
    {
        $cmdLineParser = new \Differ\CommandLineParser(new \Differ\DocoptDouble());

        $this->assertInstanceOf(\Differ\CommandLineParser::class, $cmdLineParser->setFileNames($fileNames));

        $this->assertEquals($fileNames, $cmdLineParser->setFileNames($fileNames)
                                                      ->getFileNames());
    }

    public function testExecute()
    {
        $parser = new \Differ\DocoptDouble();
        $cmdLineParser = new \Differ\CommandLineParser($parser);

        ob_start();
        $cmdLineParser->execute($cmdLineParser);
        $outputBuffer = ob_get_clean();

        $this->assertEquals(
            "",
            $outputBuffer
        );
    }

    public function testPlainExecute()
    {
        $parser = new \Differ\DocoptDouble("plain");
        $cmdLineParser = new \Differ\CommandLineParser($parser);

        ob_start();
        $cmdLineParser->execute($cmdLineParser);
        $outputBuffer = ob_get_clean();

        $this->assertEquals(
            "",
            $outputBuffer
        );
    }

    public function testSetFormat()
    {
        $parser = new \Differ\DocoptDouble("plain");
        $cmdLineParser = new \Differ\CommandLineParser($parser);

        $outputFormat = $cmdLineParser->setFormat("stylish")
                                    ->getFormat();

        $this->assertEquals(
            "stylish",
            $outputFormat
        );
    }
}
