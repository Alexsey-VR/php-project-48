<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
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

    protected function setUp(): void
    {
        $this->fileNames['JSON'] = [
            "FILE1" => __DIR__ . "/../file1.json",
            "FILE2" => __DIR__ . "/../file2.json"
        ];
        $this->fileNames['YAML'] = [
            "FILE1" => __DIR__ . "/../file1.yaml",
            "FILE2" => __DIR__ . "/../file2.yaml"
        ];
        $this->fileNames['Exception'] = [
            "FILE1" => __DIR__ . "/../fixtures/file1.txt",
            "FILE2" => __DIR__ . "/../file2.yaml"
        ];
    }

    public function testSetFileReader()
    {
        $diffCommand = new FilesDiffCommand();

        $this->assertInstanceOf(
            FilesDiffCommand::class,
            $diffCommand->setFileReader(new FileReader()));
    }

    public function testExecuteForJSON()
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => $this->fileNames['JSON']
            ]
        );

        $diffCommand = new FilesDiffCommand();

        $resultContent = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getFilesContent();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesJSONContent.txt",
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

    public function testExecuteForException()
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => $this->fileNames['Exception']
            ]
        );

        $diffCommand = new FilesDiffCommand();

        $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/unknown files format: use json, yaml \(yml\) enstead\\n/");

        $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser);
    }
}
