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
class StylishCommandTest extends TestCase
{
    public static function getParserArguments(): array
    {
        return [
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1Entry.json",
                    "FILE2" => __DIR__ . "/../fixtures/file2Entry.json"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesJSONContent.txt",
                'outputFormat' => 'STYLISH'
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1Entry.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2Entry.yaml"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesYAMLContent.txt",
                'outputFormat' => 'stylish'
            ]
        ];
    }

    #[DataProvider('getParserArguments')]
    public function testExecute($fileNamesInput, $contentFilePath, $outputFormat)
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput
            ]
        );

        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader()
        );
        $diffCommand = new FilesDiffCommand();
        $parseCommand = $commandFactory->getCommand("parse");
        $parseCommand->setFormat($outputFormat);

        $formatCommand = $commandFactory->getCommand("format");
        $stylishCommand = $formatCommand->execute($parseCommand);

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
