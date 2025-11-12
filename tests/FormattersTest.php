<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\Parsers\DocoptDouble;
use Differ\Factories\CommandFactory;
use Differ\Factories\Formatters;

#[CoversClass(CommandFactory::class)]
#[CoversClass(\Differ\Parsers\CommandLineParser::class)]
#[CoversClass(\Differ\Parsers\FileParser::class)]
#[CoversClass(Formatters::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(\Differ\FilesDiffCommand::class)]
#[CoversClass(\Differ\FileReader::class)]
#[CoversMethod(\Differ\FilesDiffCommand::class, 'execute')]
#[CoversClass(\Differ\DifferException::class)]
#[CoversClass(\Differ\Formatters\StylishCommand::class)]
#[CoversClass(\Differ\Formatters\PlainCommand::class)]
#[CoversClass(\Differ\Formatters\JSONCommand::class)]
#[CoversClass(\Differ\DisplayCommand::class)]
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
            \Differ\Parsers\CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput,
                'getFormat' => $outputFormat
            ]
        );

        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new \Differ\FileReader(),
            new Formatters()
        );

        $diffCommand = $commandFactory->createCommand("difference");
        $fileParser = $commandFactory->createCommand("parseFile");
        $resultContent1Descriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent1Descriptor));

        $resultContent2Descriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent2Descriptor));

        $resultDifferenceDescriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getDifferenceDescriptor();

        $this->assertTrue(is_array($resultDifferenceDescriptor));

        $resultDiffs = $diffCommand->execute($cmdLineParser, $fileParser);

        $formatCommand = $commandFactory->createCommand(
            strtolower($cmdLineParser->getFormat())
        );
        $jsonCommand = $formatCommand->execute($resultDiffs);

        $displayCommand = $commandFactory->createCommand("show");
        $contentJSON = $displayCommand->setFormatter($jsonCommand)
                                    ->getFilesContent();

        $this->assertStringEqualsFile(
            $contentFilePath,
            $contentJSON
        );

        $resultJSON = $displayCommand->setFormatter($jsonCommand)
                                    ->getFilesDiffs();

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
            \Differ\Parsers\CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput,
                'getFormat' => 'undefined'
            ]
        );

        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new \Differ\FileReader(),
            new Formatters()
        );

        $parseCommand = $commandFactory->createCommand("parseCMDLine");

        $diffCommand = $commandFactory->createCommand("difference");
        $fileParser = $commandFactory->createCommand("parseFile");
        $resultContent1Descriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent1Descriptor));

        $resultContent2Descriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getContent1Descriptor();

        $this->assertTrue(is_array($resultContent2Descriptor));

        $resultDifferenceDescriptor = $diffCommand->execute($cmdLineParser, $fileParser)
                                 ->getDifferenceDescriptor();

        $this->assertTrue(is_array($resultDifferenceDescriptor));

        $resultDiffs = $diffCommand->execute($cmdLineParser, $fileParser);

         $this->expectException(\Differ\DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown command factory option/");

        $formatCommand = $commandFactory->createCommand(
            strtolower($cmdLineParser->getFormat())
        );
    }
}
