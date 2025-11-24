<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
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

use function Differ\Differ\genDiff;

#[CoversNothing]
class GendiffHelperTest extends TestCase
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
    public function testHelperDiffer($formatter, $filePath)
    {
        $outputBuffer = "";
        if (is_null($formatter)) {
            $outputBuffer = genDiff(
                $_ENV['FIXTURES_PATH'] . "/file1.json",
                $_ENV['FIXTURES_PATH'] . "/file2.json"
            );
        } else {
            $outputBuffer = genDiff(
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
