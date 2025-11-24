<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\Tests\Fixtures\DocoptDouble;
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
use Differ\Differ;

#[CoversClass(CommandFactory::class)]
#[CoversClass(Formatters::class)]
#[CoversClass(FileParser::class)]
#[CoversClass(CommandLineParser::class)]
#[CoversClass(FileReader::class)]
#[CoversClass(FilesDiffCommand::class)]
#[CoversClass(StylishCommand::class)]
#[CoversClass(PlainCommand::class)]
#[CoversClass(JSONCommand::class)]
#[CoversClass(DisplayCommand::class)]
#[CoversClass(Differ::class)]
#[CoversMethod(Differ::class, "run")]
#[CoversMethod(Differ::class, "genDiff")]
class GendiffConsoleTest extends TestCase
{
    public static function getTestData(): array
    {
        return [
            [
                "formatter" => null,
                "filePath" => $_ENV['FIXTURES_PATH'] . "/filesRecursiveStylishDiffs.txt"
            ],
            [
                "formatter" => "stylish",
                "filePath" => $_ENV['FIXTURES_PATH'] . "/filesRecursiveStylishDiffs.txt"
            ],
            [
                "formatter" => "plain",
                "filePath" => $_ENV['FIXTURES_PATH'] . "/filesRecursivePlainDiffs.txt"
            ],
            [
                "formatter" => "json",
                "filePath" => $_ENV['FIXTURES_PATH'] . "/filesRecursiveJSONDiffs.json"
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
        $differ = new Differ($commandFactory);
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
        $differ = new Differ(
            $commandFactory = new CommandFactory(
                $commandLineParser,
                new FileReader(),
                new Formatters()
            )
        );

        $outputBuffer = "";
        if (is_null($formatter)) {
            $outputBuffer = $differ->gendiff(
                $_ENV['FIXTURES_PATH'] . "/file1.json",
                $_ENV['FIXTURES_PATH'] . "/file2.json"
            );
        } else {
            $outputBuffer = $differ->genDiff(
                $_ENV['FIXTURES_PATH'] . "/file1.json",
                $_ENV['FIXTURES_PATH'] . "/file2.json",
                $formatter
            );
        }

        $this->assertStringEqualsFile(
            $filePath,
            $outputBuffer
        );
    }
}
