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
use Differ\Tests\Fixtures\FixturesHelper;

use function Differ\Differ\genDiff;

#[CoversNothing]
class GendiffHelperTest extends TestCase
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
                "filePath" => "{$fullFixturesPath}/filesRecursiveJSONDiffs.json"
            ]
        ];
    }

    #[DataProvider('getTestData')]
    public function testHelperDiffer($formatter, $filePath)
    {
        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        if (is_null($formatter)) {
            $outputBuffer = genDiff(
                "{$fullFixturesPath}/file1.json",
                "{$fullFixturesPath}/file2.json"
            );
        } else {
            $outputBuffer = genDiff(
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
