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
use Differ\Tests\Fixtures\FixturesHelper;

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
        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        return [
            [
                'fileNamesInput' => [
                    "FILE1" => "{$fullFixturesPath}/file1.json",
                    "FILE2" => "{$fullFixturesPath}/file2.json"
                ],
                'contentFilePath' => "{$fullFixturesPath}/filesStylishRecursiveJSONContent.txt",
                'outputFormat' => 'STYLISH',
                'outputDiffsPath' => "{$fullFixturesPath}/filesRecursiveStylishDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => "{$fullFixturesPath}/file1.yaml",
                    "FILE2" => "{$fullFixturesPath}/file2.yaml"
                ],
                'contentFilePath' => "{$fullFixturesPath}/filesStylishRecursiveYAMLContent.txt",
                'outputFormat' => 'stylish',
                'outputDiffsPath' => "{$fullFixturesPath}/filesRecursiveStylishDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => "{$fullFixturesPath}/file1.json",
                    "FILE2" => "{$fullFixturesPath}/file2.json"
                ],
                'contentFilePath' => "{$fullFixturesPath}/filesPlainRecursiveJSONContent.txt",
                'outputFormat' => 'plain',
                'outputDiffsPath' => "{$fullFixturesPath}/filesRecursivePlainDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => "{$fullFixturesPath}/file1.yaml",
                    "FILE2" => "{$fullFixturesPath}/file2.yaml"
                ],
                'contentFilePath' => "{$fullFixturesPath}/filesPlainRecursiveYAMLContent.txt",
                'outputFormat' => 'plain',
                'outputDiffsPath' => "{$fullFixturesPath}/filesRecursivePlainDiffs.txt"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => "{$fullFixturesPath}/file1.json",
                    "FILE2" => "{$fullFixturesPath}/file2.json"
                ],
                'contentFilePath' => "{$fullFixturesPath}/filesJSONRecursiveJSONContent.txt",
                'outputFormat' => 'json',
                'outputDiffsPath' => "{$fullFixturesPath}/filesRecursiveJSONDiffs.json"
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => "{$fullFixturesPath}/file1.yaml",
                    "FILE2" => "{$fullFixturesPath}/file2.yaml"
                ],
                'contentFilePath' => "{$fullFixturesPath}/filesJSONRecursiveYAMLContent.txt",
                'outputFormat' => 'json',
                'outputDiffsPath' => "{$fullFixturesPath}/filesRecursiveJSONDiffs.json"
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
        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        $fileNamesInput = [
            "FILE1" => "{$fullFixturesPath}/file1.yaml",
            "FILE2" => "{$fullFixturesPath}/file2.yaml"
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
