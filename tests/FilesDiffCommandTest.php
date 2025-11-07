<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(\Differ\FilesDiffCommand::class)]
#[CoversClass(\Differ\FileReader::class)]
#[CoversMethod(\Differ\FilesDiffCommand::class, 'execute')]
#[CoversClass(\Differ\DifferException::class)]
class FilesDiffCommandTest extends TestCase
{
    private $fileNames;

    public function testSetFileReader()
    {
        $diffCommand = new \Differ\FilesDiffCommand(new \Differ\FileReader());

        $this->assertInstanceOf(
            \Differ\FilesDiffCommand::class,
            $diffCommand
        );
    }

    public static function getFileNames(): array
    {
        return [
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1Entry.json",
                    "FILE2" => __DIR__ . "/../fixtures/file2Entry.json"
                ],
                'outputFilePath' => __DIR__ . "/../fixtures/filesJSONContent.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1Entry.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2Entry.yaml"
                ],
                'outputFilePath' => __DIR__ . "/../fixtures/filesYAMLContent.txt"
            ]
        ];
    }

    #[DataProvider('getFileNames')]
    public function testExecute($fileNamesInput, $outputFilePath)
    {
        $cmdLineParser = $this->createConfiguredStub(
            \Differ\CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput
            ]
        );

        $diffCommand = new \Differ\FilesDiffCommand(new \Differ\FileReader());

        $resultContent1Descriptor = $diffCommand->execute($cmdLineParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent1Descriptor));

        $resultContent2Descriptor = $diffCommand->execute($cmdLineParser)
                                 ->getContent2Descriptor();

        $this->assertTrue(is_array($resultContent2Descriptor));

        $resultDifferenceDescriptor = $diffCommand->execute($cmdLineParser)
                                 ->getDifferenceDescriptor();

        $this->assertTrue(is_array($resultDifferenceDescriptor));
    }

    public function testExecuteForException()
    {
        $cmdLineParser = $this->createConfiguredStub(
            \Differ\CommandLineParser::class,
            [
                'getFileNames' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.txt",
                    "FILE2" => __DIR__ . "/../fixtures/file2Entry.yaml"
                ]
            ]
        );

        $diffCommand = new \Differ\FilesDiffCommand(new \Differ\FileReader());

        $this->expectException(\Differ\DifferException::class);
        $this->expectExceptionMessageMatches("/unknown files format: use json, yaml \(yml\) enstead\\n/");

        $diffCommand->execute($cmdLineParser);
    }
}
