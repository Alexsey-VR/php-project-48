<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\Parsers\DocoptDouble;
use Differ\Parsers\FileParser;
use Differ\Parsers\CommandLineParser;
use Differ\Readers\FileReader;
use Differ\Factories\CommandFactory;
use Differ\Factories\Formatters;
use Differ\Differs\FilesDiffCommand;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\formatters\JSONCommand;
use Differ\Displays\DisplayCommand;
use Differ\ConsoleApp;

#[CoversClass(CommandFactory::class)]
#[CoversClass(Formatters::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(FileParser::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(FileReader::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(StylishCommand::class)]
#[CoversClass(PlainCommand::class)]
#[CoversClass(JSONCommand::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(ConsoleApp::class)]
#[CoversMethod(ConsoleApp::class, "run")]
class GendiffConsoleTest extends TestCase
{
    public static function getTestData(): array
    {
        return [
            [
                "formatter" => null,
                "filePath" => __DIR__ . "/../fixtures/filesRecursiveStylishDiffs.txt"
            ],
            [
                "formatter" => "stylish",
                "filePath" => __DIR__ . "/../fixtures/filesRecursiveStylishDiffs.txt"
            ],
            [
                "formatter" => "plain",
                "filePath" => __DIR__ . "/../fixtures/filesRecursivePlainDiffs.txt"
            ],
            [
                "formatter" => "json",
                "filePath" => __DIR__ . "/../fixtures/filesRecursiveJSONDiffs.json"
            ]
        ];
    }

    #[DataProvider('getTestData')]
    public function testConsoleDiffer($formatter, $filePath)
    {
        $commandLineParser = is_null($formatter) ? new DocoptDouble() : new DocoptDouble($formatter);
        $commandFactory = new CommandFactory(
            $commandLineParser,
            new FileReader(),
            new Formatters()
        );

        ob_start();
        $differ = new ConsoleApp($commandFactory);
        $differ->run();
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            $filePath,
            $outputBuffer
        );
    }

    #[DataProvider('getTestData')]
    public function testAPIDiffer($formatter, $filePath)
    {
        $commandLineParser = is_null($formatter) ? new DocoptDouble() : new DocoptDouble($formatter);
        $differ = new ConsoleApp(
            $commandFactory = new CommandFactory(
                $commandLineParser,
                new FileReader(),
                new Formatters()
            )
        );
        $outputBuffer = "";
        if (is_null($formatter)) {
            $outputBuffer = $differ->gendiff(
                __DIR__ . "/../fixtures/file1.json",
                __DIR__ . "/../fixtures/file2.json"
            );
        } else {
            $outputBuffer = $differ->gendiff(
                __DIR__ . "/../fixtures/file1.json",
                __DIR__ . "/../fixtures/file2.json",
                $formatter
            );
        }

        $this->assertStringEqualsFile(
            $filePath,
            $outputBuffer
        );
    }
}
