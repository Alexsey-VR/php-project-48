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
        $this->fileNames = [
            "FILE1" => __DIR__ . "/../file1.json",
            "FILE2" => __DIR__ . "/../file2.json"
        ];
    }

    public function testSetFileReader()
    {
        $diffCommand = new FilesDiffCommand();

        $this->assertInstanceOf(
            FilesDiffCommand::class,
            $diffCommand->setFileReader(new FileReader()));
    }

    public function testExecute()
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => $this->fileNames
            ]
        );

        $diffCommand = new FilesDiffCommand();

        $resultContent = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser)
                                 ->getFilesContent();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesContent.txt",
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
