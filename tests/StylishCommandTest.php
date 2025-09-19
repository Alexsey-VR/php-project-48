<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\FileReader;
use Differ\DifferException;
use Differ\Formatters\StylishCommand;

#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(FilesDiffCommand::class, 'setFileReader')]
#[CoversMethod(FilesDiffCommand::class, 'execute')]
#[CoversClass(DifferException::class)]
#[CoversClass(StylishCommand::class)]
class StylishCommandTest extends TestCase
{
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
        $stylishCommand = new StylishCommand();

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

        $resultDiffs = $diffCommand->setFileReader(new FileReader())
                                 ->execute($cmdLineParser);
        $resultStylish = $stylishCommand->execute($resultDiffs)
                                 ->getFilesDiffs();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesDiffs.txt",
            $resultStylish
        );

    }
}
