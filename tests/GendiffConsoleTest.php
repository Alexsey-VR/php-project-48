<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Differ\Parsers\DocoptDouble;
use Differ\Parsers\FileParser;
use Differ\Parsers\CommandLineParser;
use Differ\Readers\FileReader;
use Differ\Factories\CommandFactory;
use Differ\Factories\Formatters;
use Differ\Differ\FilesDiffCommand;
use Differ\Differ\Differ;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\formatters\JSONCommand;
use Differ\Displays\DisplayCommand;
use Differ\Tests\Fixtures\FixturesHelper;

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
#[CoversClass(Differ::class)]
#[CoversMethod(Differ::class, "run")]
#[CoversMethod(Differ::class, "genDiff")]
class GendiffConsoleTest extends TestCase
{
    public static function getTestData(): array
    {
        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        return [
            [
                "formatter" => null,
                "filePath" => "{$fullFixturesPath}/filesRecursiveStylishDiffs.txt"
            ],
            [
                "formatter" => "stylish",
                "filePath" => "{$fullFixturesPath}/filesRecursiveStylishDiffs.txt"
            ],
            [
                "formatter" => "plain",
                "filePath" => "{$fullFixturesPath}/filesRecursivePlainDiffs.txt"
            ],
            [
                "formatter" => "json",
                "filePath" =>"{$fullFixturesPath}/filesRecursiveJSONDiffs.json"
            ]
        ];
    }

    #[DataProvider('getTestData')]
    public function testConsoleDiffer($formatter, $filePath)
    {
        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        $commandLineParser = is_null($formatter) ?
            new DocoptDouble("{$fullFixturesPath}/file1.json", "{$fullFixturesPath}/file2.json")
            :
            new DocoptDouble("{$fullFixturesPath}/file1.json", "{$fullFixturesPath}/file2.json", $formatter);
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
            new CommandFactory(
                $commandLineParser,
                new FileReader(),
                new Formatters()
            )
        );

        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        if (is_null($formatter)) {
            $outputBuffer = $differ->gendiff(
                "{$fullFixturesPath}/file1.json",
                "{$fullFixturesPath}/file2.json"
            );
        } else {
            $outputBuffer = $differ->genDiff(
                "{$fullFixturesPath}/file1.json",
                "{$fullFixturesPath}/file2.json",
                $formatter
            );
        }

        $this->assertStringEqualsFile(
            $filePath,
            $outputBuffer
        );
    }
}
