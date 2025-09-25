<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\Formatters;
use Differ\FileReader;
use Differ\DifferException;
use Differ\DocoptDouble;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlaneCommand;
use Differ\Formatters\JSONCommand;

#[CoversClass(CommandFactory::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(Formatters::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(FilesDiffCommand::class, 'setFileReader')]
#[CoversMethod(FilesDiffCommand::class, 'execute')]
#[CoversClass(DifferException::class)]
#[CoversClass(StylishCommand::class)]
#[CoversClass(PlaneCommand::class)]
#[CoversClass(JSONCommand::class)]
class FormattersTest extends TestCase
{
    public static function getParserArguments(): array
    {
        return [
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.json",
                    "FILE2" => __DIR__ . "/../fixtures/file2.json"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesStylishRecursiveJSONContent.txt",
                'outputFormat' => 'STYLISH',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursiveStylishDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2.yaml"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesStylishRecursiveYAMLContent.txt",
                'outputFormat' => 'stylish',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursiveStylishDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.json",
                    "FILE2" => __DIR__ . "/../fixtures/file2.json"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesPlaneRecursiveJSONContent.txt",
                'outputFormat' => 'plane',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursivePlaneDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2.yaml"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesPlaneRecursiveYAMLContent.txt",
                'outputFormat' => 'plane',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursivePlaneDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.json",
                    "FILE2" => __DIR__ . "/../fixtures/file2.json"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesJSONRecursiveJSONContent.txt",
                'outputFormat' => 'json',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursiveJSONDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2.yaml"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesJSONRecursiveYAMLContent.txt",
                'outputFormat' => 'json',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursiveJSONDiffs.txt"
            ]
        ];
    }

    #[DataProvider('getParserArguments')]
    public function testExecute($fileNamesInput, $contentFilePath, $outputFormat, $outputDiffsPath)
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput,
                'getFormat' => $outputFormat
            ]
        );

        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader()
        );

        $parseCommand = $commandFactory->getCommand("parse");

        $diffCommand = $commandFactory->getCommand("difference");

        $resultContent1Descriptor = $diffCommand->execute($cmdLineParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent1Descriptor));

        $resultContent2Descriptor = $diffCommand->execute($cmdLineParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent2Descriptor));

        $resultDifferenceDescriptor = $diffCommand->execute($cmdLineParser)
                                 ->getDifferenceDescriptor();

        $this->assertTrue(is_array($resultDifferenceDescriptor));

        $resultDiffs = $diffCommand->execute($cmdLineParser);

        $formatCommand = $commandFactory->getCommand("format");
        $jsonCommand = $formatCommand->selectFormat($cmdLineParser)
                                    ->execute($resultDiffs);

        $contentJSON = $jsonCommand->getFilesContent();

        $this->assertStringEqualsFile(
            $contentFilePath,
            $contentJSON
        );

        $resultJSON = $jsonCommand->getFilesDiffs();

        $this->assertStringEqualsFile(
            $outputDiffsPath,
            $resultJSON
        );
    }

    public function testFormatException()
    {
        $fileNamesInput = [
            "FILE1" => __DIR__ . "/../fixtures/file1.yaml",
            "FILE2" => __DIR__ . "/../fixtures/file2.yaml"
        ];

        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput,
                'getFormat' => 'undefined'
            ]
        );

        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader()
        );

        $parseCommand = $commandFactory->getCommand("parse");

        $diffCommand = $commandFactory->getCommand("difference");

        $resultContent1Descriptor = $diffCommand->execute($cmdLineParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent1Descriptor));

        $resultContent2Descriptor = $diffCommand->execute($cmdLineParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent2Descriptor));

        $resultDifferenceDescriptor = $diffCommand->execute($cmdLineParser)
                                 ->getDifferenceDescriptor();

        $this->assertTrue(is_array($resultDifferenceDescriptor));

        $resultDiffs = $diffCommand->execute($cmdLineParser);

        $formatCommand = $commandFactory->getCommand("format");

        $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/input error: unknown output format\\nUse gendiff -h\\n/");

        $jsonCommand = $formatCommand->selectFormat($cmdLineParser)
                                    ->execute($resultDiffs);
    }
}
