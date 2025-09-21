<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\CommandFactory;
use Differ\FileReader;
use Differ\DifferException;
use Differ\DocoptDouble;
use Differ\Formatters\PlaneCommand;

#[CoversClass(CommandFactory::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(FileReader::class)]
#[CoversMethod(FilesDiffCommand::class, 'setFileReader')]
#[CoversMethod(FilesDiffCommand::class, 'execute')]
#[CoversClass(DifferException::class)]
#[CoversClass(PlaneCommand::class)]
class PlaneCommandTest extends TestCase
{
    public static function getParserArguments(): array
    {
        return [
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.json",
                    "FILE2" => __DIR__ . "/../fixtures/file2.json"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesJSONContent.txt",
                'outputFormat' => 'plane'
            ],
            [
                'fileNamesInput' => [
                    "FILE1" => __DIR__ . "/../fixtures/file1.yaml",
                    "FILE2" => __DIR__ . "/../fixtures/file2.yaml"
                ],
                'contentFilePath' => __DIR__ . "/../fixtures/filesYAMLContent.txt",
                'outputFormat' => 'plane'
            ]
        ];
    }

    #[DataProvider('getParserArguments')]
    public function testExecute($fileNamesInput, $contentFilePath, $outputFormat)
    {
        $cmdLineParser = $this->createConfiguredStub(
            CommandLineParser::class,
            [
                'getFileNames' => $fileNamesInput,
            ]
        );

        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader()
        );
        $diffCommand = new FilesDiffCommand();
        $planeCommand = $commandFactory->getCommand($outputFormat);

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
        $resultStylish = $planeCommand->execute($resultDiffs)
                                 ->getFilesDiffs();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursivePlaneDiffs.txt",
            $resultStylish
        );

    }
}
