<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\Tests\Fixtures\DocoptDouble;

#[CoversClass(\Differ\Parsers\CommandLineParser::class)]
#[CoversMethod(\Differ\Parsers\CommandLineParser::class, 'setFileNames')]
#[CoversMethod(\Differ\Parsers\CommandLineParser::class, 'getFileNames')]
class CommandLineParserTest extends TestCase
{
    public static function getFiles(): array
    {
        return [
            [
                "fileNames" => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1Entry.json",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2Entry.json"
                ]
            ],
            [
                "fileNames" => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1Entry.yaml",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2Entry.yaml"
                ]
            ]
        ];
    }

    #[DataProvider('getFiles')]
    public function testFileNames($fileNames)
    {
        $cmdLineParser = new \Differ\Parsers\CommandLineParser(new DocoptDouble());

        $this->assertInstanceOf(\Differ\Parsers\CommandLineParser::class, $cmdLineParser->setFileNames($fileNames));

        $this->assertEquals($fileNames, $cmdLineParser->setFileNames($fileNames)
                                                      ->getFileNames());
    }

    public function testExecute()
    {
        $parser = new DocoptDouble();
        $cmdLineParser = new \Differ\Parsers\CommandLineParser($parser);

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
        $parser = new DocoptDouble("plain");
        $cmdLineParser = new \Differ\Parsers\CommandLineParser($parser);

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
        $parser = new DocoptDouble("plain");
        $cmdLineParser = new \Differ\Parsers\CommandLineParser($parser);

        $outputFormat = $cmdLineParser->setFormat("stylish")
                                    ->getFormat();

        $this->assertEquals(
            "stylish",
            $outputFormat
        );
    }
}
