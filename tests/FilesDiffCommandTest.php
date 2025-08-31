<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\FileReader;

#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(FilesDiffCommand::class, 'setFileReader')]
#[CoversMethod(FilesDiffCommand::class, 'execute')]
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
}
