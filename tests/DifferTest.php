<?php

namespace Differ\Differ;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DifferTest extends TestCase
{
    public static function filePathTests(): array
    {
        $diffText = 
        "{\n" .
        "    hexlet.io\n" .
        "  - 50\n" .
        "  + 20\n" .
        "  - 123.234.53.22\n" .
        "  - \n" .
        "}\n";
        return 
        [
            [$diffText, ["file1.json", "file2.json"]],
            [$diffText, [__DIR__ . "/../file1.json", __DIR__ . "/../file2.json"]]
        ];
    }

    #[DataProvider('filePathTests')]
    public function testDiffer(string $result, array $filePaths) {
        print_r("Current directory: " . __DIR__);
        $this->assertEquals($result, genDiff($filePaths[0], $filePaths[1]));
    }
}