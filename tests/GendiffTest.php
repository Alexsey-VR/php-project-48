<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use function Differ\runGendiff;
use Differ\DocoptDouble;

#[CoversNothing]
class GendiffTest extends TestCase
{
    public function testRunGendiff()
    {
        $commandFactory = new CommandFactory(
            new DocoptDouble(),
            new FileReader(),
            new StylishCommand()
        );

        ob_start();
        runGendiff($commandFactory);
        $outputBuffer = ob_get_clean();

        $this->assertStringEqualsFile(
            __DIR__ . "/../fixtures/filesRecursiveDiffs.txt",
            $outputBuffer
        );
    }
}
