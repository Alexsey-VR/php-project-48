<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use \Differ\Parsers\DocoptDouble;

#[CoversClass(\Differ\DisplayCommand::class)]
#[CoversMethod(\Differ\DisplayCommand::class, 'execute')]
#[CoversClass(\Differ\DifferException::class)]
#[CoversClass(\Differ\CommandFactory::class)]
#[CoversClass(DocoptDouble::class)]
#[CoversClass(\Differ\FileReader::class)]
class DisplayCommandTest extends TestCase
{
    private $commandFactory;
    private $displayCommand;
    private $formattersStub;
    private $formatters;
    private $testKeys;
    private $testStrings;
    private const string STRING_POSTFIX = "String";

    protected function setUp(): void
    {
        $this->commandFactory = new \Differ\CommandFactory(
            new DocoptDouble(),
            new \Differ\FileReader(),
            new \Differ\Formatters()
        );

        $this->displayCommand = $this->commandFactory->createCommand("show");

        $this->formattersStub = $this->createConfiguredStub(
            \Differ\Formatters\JSONCommand::class,
            [
                "getContentString" => $this->displayCommand::AVAILABLE_MODES["content"] . self::STRING_POSTFIX,
                "getDiffsString" => $this->displayCommand::AVAILABLE_MODES["differents"] . self::STRING_POSTFIX
            ]
        );

        $this->testKeys = $this->displayCommand::AVAILABLE_MODES;
        $this->testStrings = array_reduce(
            $this->testKeys,
            function ($accum, $item) {
                $accum[$item]  = $item . self::STRING_POSTFIX;
                return $accum;
            },
            []
        );
    }

    public function testInstance()
    {
        $this->assertInstanceOf(\Differ\DisplayCommand::class, $this->displayCommand);
    }

    public function testDisplay()
    {
        foreach ($this->testKeys as $key) {
            $this->displayCommand->setMode($key);

            ob_start();
            $this->displayCommand->execute($this->formattersStub);
            $formattersOutput = ob_get_clean();
            $this->assertEquals($formattersOutput, $this->testStrings[$key]);
        }
    }

    public function testUnknownDisplayMode()
    {
        $this->expectException(\Differ\DifferException::class);
        $this->expectExceptionMessageMatches("/internal error: unknown mode for display\\n/");

        $this->displayCommand->setMode("undefined");
    }
}
