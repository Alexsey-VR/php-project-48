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
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;
use Differ\DisplayCommand;

#[CoversClass(CommandFactory::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(Formatters::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(FilesDiffCommand::class, 'execute')]
#[CoversClass(DifferException::class)]
#[CoversClass(StylishCommand::class)]
#[CoversClass(PlainCommand::class)]
#[CoversClass(JSONCommand::class)]
#[CoversClass(DisplayCommand::class)]
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
                'contentFilePath' => __DIR__ . "/../fixtures/filesPlainRecursiveJSONContent.txt",
                'outputFormat' => 'plain',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursivePlainDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2.yaml"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesPlainRecursiveYAMLContent.txt",
                'outputFormat' => 'plain',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursivePlainDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.json",
                    "FILE2" => __DIR__ . "/../fixtures/file2.json"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesJSONRecursiveJSONContent.txt",
                'outputFormat' => 'json',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursiveJSONDiffs.json"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2.yaml"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesJSONRecursiveYAMLContent.txt",
                'outputFormat' => 'json',
                'outputDiffsPath' => __DIR__ . "/../fixtures/filesRecursiveJSONDiffs.json"
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
            new FileReader(),
            new Formatters()
        );

        $diffCommand = $commandFactory->createCommand("difference");

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

        $formatCommand = $commandFactory->createCommand(
            strtolower($cmdLineParser->getFormat())
        );
        $jsonCommand = $formatCommand->execute($resultDiffs);

        $displayCommand = $commandFactory->createCommand("show");
        $contentJSON = $displayCommand->setFormatter($jsonCommand)
                                    ->getContentString();

        $this->assertStringEqualsFile(
            $contentFilePath,
            $contentJSON
        );

        $resultJSON = $displayCommand->setFormatter($jsonCommand)
                                    ->getDiffsString();

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
            new FileReader(),
            new Formatters()
        );

        $parseCommand = $commandFactory->createCommand("parse");

        $diffCommand = $commandFactory->createCommand("difference");

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

         $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown command factory option/");

        $formatCommand = $commandFactory->createCommand(
            strtolower($cmdLineParser->getFormat())
        );
    }
}
