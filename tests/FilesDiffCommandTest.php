<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\FileReader;
use Differ\DifferException;

#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(FilesDiffCommand::class, 'setFileReader')]
#[CoversMethod(FilesDiffCommand::class, 'execute')]
#[CoversClass(DifferException::class)]
class FilesDiffCommandTest extends TestCase
{
    private $fileNames;

    public function testSetFileReader()
    {
        $diffCommand = new FilesDiffCommand();

        $this->assertInstanceOf(
            FilesDiffCommand::class,
            $diffCommand->setFileReader(new FileReader()));
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
            CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput
            ]
        );

        $diffCommand = new FilesDiffCommand();

        $resultContent1Descriptor = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent1Descriptor));

        $resultContent2Descriptor = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent2Descriptor));

        $resultDifferenceDescriptor = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getDifferenceDescriptor();

        $this->assertTrue(is_array($resultDifferenceDescriptor));
    }
    
    public function testExecuteForException()
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.txt",
                    "FILE2" => __DIR__ . "/../fixtures/file2Entry.yaml"
                ]
            ]
        );

        $diffCommand = new FilesDiffCommand();

        $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/unknown files format: use json, yaml \(yml\) enstead\\n/");

        $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser);
    }
}
