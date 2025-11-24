<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\Differ\FilesDiffCommand;
use Differ\Readers\FileReader;
use Differ\Exceptions\DifferException;
use Differ\Parsers\FileParser;

#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversClass(FileParser::class)]
#[CoversMethod(FilesDiffCommand::class, 'execute')]
#[CoversClass(DifferException::class)]
class FilesDiffCommandTest extends TestCase
{
    private $fileNames;

    public function testSetFileReader()
    {
        $diffCommand = new FilesDiffCommand(new FileReader());

        $this->assertInstanceOf(
            FilesDiffCommand::class,
            $diffCommand
        );
    }

    public static function getFileNames(): array
    {
        return [
            [
                'fileNamesInput' => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1Entry.json",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2Entry.json"
                ],
                'outputFilePath' => $_ENV['FIXTURES_PATH'] . "/filesJSONContent.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1Entry.yaml",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2Entry.yaml"
                ],
                'outputFilePath' => $_ENV['FIXTURES_PATH'] . "/filesYAMLContent.txt"
            ]
        ];
    }

    #[DataProvider('getFileNames')]
    public function testExecute($fileNamesInput, $outputFilePath)
    {
        $cmdLineParser = $this->createConfiguredStub(
            \Differ\Parsers\CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput
            ]
        );

        $diffCommand = new FilesDiffCommand(
            new FileReader()
        );
        $fileParser = new FileParser();

        $resultContent1Descriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent1Descriptor));

        $resultContent2Descriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getContent2Descriptor();

        $this->assertTrue(is_array($resultContent2Descriptor));

        $resultDifferenceDescriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getDifferenceDescriptor();

        $this->assertTrue(is_array($resultDifferenceDescriptor));
    }

    public function testExecuteForException()
    {
        $cmdLineParser = $this->createConfiguredStub(
            \Differ\Parsers\CommandLineParser::class,
            [
                'getFileNames' => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1.txt",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2Entry.yaml"
                ]
            ]
        );

        $diffCommand = new FilesDiffCommand(new FileReader());
        $fileParser = new FileParser();

        $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/unknown files format: use json, yaml \(yml\) enstead\\n/");

        $diffCommand->execute($cmdLineParser, $fileParser);
    }
}
