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

 /*   protected function setUp(): void
    { */
        /*
        $this->fileNames['JSON'] = [
            "FILE1" => __DIR__ . "/../fixtures/file1.json",
            "FILE2" => __DIR__ . "/../fixtures/file2.json"
        ];
        */
        /*
        $this->fileNames['JSON'] = [
            "FILE1" => __DIR__ . "/../fixtures/file1Entry.json",
            "FILE2" => __DIR__ . "/../fixtures/file2Entry.json"
        ];
        
        $this->fileNames['YAML'] = [
            "FILE1" => __DIR__ . "/../fixtures/file1Entry.yaml",
            "FILE2" => __DIR__ . "/../fixtures/file2Entry.yaml"
        ];
        */
        /*
        $this->fileNames['Exception'] = [
            "FILE1" => __DIR__ . "/../fixtures/file1.txt",
            "FILE2" => __DIR__ . "/../fixtures/file2Entry.yaml"
        ];
        */
 /*   } */
    public function testSetFileReader()
    {
        $diffCommand = new FilesDiffCommand();

        $this->assertInstanceOf(
            FilesDiffCommand::class,
            $diffCommand->setFileReader(new FileReader()));
    }

    public static function getFileNames(): array
    {
        $fileNamesData = [
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

        return $fileNamesData;
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

        $resultContent = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getFilesContent();

        $this->assertStringEqualsFile(
            $outputFilePath,
            $resultContent
        );

        $resultDiffs = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getFilesDiffs();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesDiffs.txt",
            $resultDiffs
        );
    }
/*
    public function testExecuteForYAML()
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => $this->fileNames['YAML']
            ]
        );

        $diffCommand = new FilesDiffCommand();

        $resultContent = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getFilesContent();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesYAMLContent.txt",
            $resultContent
        );

        $resultDiffs = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getFilesDiffs();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesDiffs.txt",
            $resultDiffs
        );
    }
*/
    public function testExecuteForException()
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => /*$this->fileNames['Exception']*/ [
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
