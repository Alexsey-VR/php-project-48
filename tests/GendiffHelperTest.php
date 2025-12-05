<?php

namespace Differ\Tests;

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
                "filePath" => __DIR__ . "/Fixtures/filesRecursiveStylishDiffs.txt"
            ],
            [
                "formatter" => "stylish",
                "filePath" => __DIR__ . "/Fixtures/filesRecursiveStylishDiffs.txt"
            ],
            [
                "formatter" => "plain",
                "filePath" => __DIR__ . "/Fixtures/filesRecursivePlainDiffs.txt"
            ],
            [
                "formatter" => "json",
                "filePath" => __DIR__ . "/Fixtures/filesRecursiveJSONDiffs.json"
            ]
        ];
    }

    #[DataProvider('getTestData')]
    public function testHelperDiffer($formatter, $filePath)
    {
        $outputBuffer = "";
        if (is_null($formatter)) {
            $outputBuffer = genDiff(
                __DIR__ . "/Fixtures/file1.json",
                __DIR__ . "/Fixtures/file2.json"
            );
        } else {
            $outputBuffer = genDiff(
                __DIR__ . "/Fixtures/file1.json",
                __DIR__ . "/Fixtures/file2.json",
                $formatter
            );
        }

        $this->assertStringEqualsFile(
            $filePath,
            $outputBuffer
        );
    }
}
