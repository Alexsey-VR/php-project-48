<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\Parsers\DocoptDouble;
use Differ\Parsers\CommandLineParser;
use Differ\Parsers\FileParser;
use Differ\Factories\CommandFactory;
use Differ\Factories\Formatters;
use Differ\Displays\DisplayCommand;
use Differ\Differ\FilesDiffCommand;
use Differ\Readers\FileReader;
use Differ\Exceptions\DifferException;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;

#[CoversClass(CommandFactory::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(FileParser::class)]
#[CoversClass(Formatters::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(DocoptDouble::class)]
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
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1.json",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2.json"
                ],
                'contentFilePath' => $_ENV['FIXTURES_PATH'] . "/filesStylishRecursiveJSONContent.txt",
                'outputFormat' => 'STYLISH',
                'outputDiffsPath' => $_ENV['FIXTURES_PATH'] . "/filesRecursiveStylishDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1.yaml",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2.yaml"
                ],
                'contentFilePath' => $_ENV['FIXTURES_PATH'] . "/filesStylishRecursiveYAMLContent.txt",
                'outputFormat' => 'stylish',
                'outputDiffsPath' => $_ENV['FIXTURES_PATH'] . "/filesRecursiveStylishDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1.json",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2.json"
                ],
                'contentFilePath' => $_ENV['FIXTURES_PATH'] . "/filesPlainRecursiveJSONContent.txt",
                'outputFormat' => 'plain',
                'outputDiffsPath' => $_ENV['FIXTURES_PATH'] . "/filesRecursivePlainDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1.yaml",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2.yaml"
                ],
                'contentFilePath' => $_ENV['FIXTURES_PATH'] . "/filesPlainRecursiveYAMLContent.txt",
                'outputFormat' => 'plain',
                'outputDiffsPath' => $_ENV['FIXTURES_PATH'] . "/filesRecursivePlainDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1.json",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2.json"
                ],
                'contentFilePath' => $_ENV['FIXTURES_PATH'] . "/filesJSONRecursiveJSONContent.txt",
                'outputFormat' => 'json',
                'outputDiffsPath' => $_ENV['FIXTURES_PATH'] . "/filesRecursiveJSONDiffs.json"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1.yaml",
                    "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2.yaml"
                ],
                'contentFilePath' => $_ENV['FIXTURES_PATH'] . "/filesJSONRecursiveYAMLContent.txt",
                'outputFormat' => 'json',
                'outputDiffsPath' => $_ENV['FIXTURES_PATH'] . "/filesRecursiveJSONDiffs.json"
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
            "FILE1" => $_ENV['FIXTURES_PATH'] . "/file1.yaml",
            "FILE2" => $_ENV['FIXTURES_PATH'] . "/file2.yaml"
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

         $this->expectException(DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown command factory option/");

        $formatCommand = $commandFactory->createCommand(
            strtolower($cmdLineParser->getFormat())
        );
    }
}
