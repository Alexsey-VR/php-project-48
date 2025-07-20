<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Differ\DisplayCommand;
use Fixtures\CommandLineParsersStub;

#[CoversClass(DisplayCommand::class)]
class DisplayCommandTest extends TestCase
{
    public $displayCmd;
    private $file1Content;
    private $file2Content;

    protected function setUp(): void
    {
        $this->displayCmd = new DisplayCommand();
    
        $this->file1Content =
        [
            "id" => "none",
            "host" => "hexlet.io",
            "timeout" => 50
        ];
        $this->file2Content =
        [
            "timeout" => 20,
            "verbose" => 1,
            "host" => "hexlet.io"
        ];
    }

    public function testInstance()
    {
        $this->assertInstanceOf(DisplayCommand::class, $this->displayCmd);
    }

    public function testExecute()
    {
        $filesDiffCmd = $this->createConfiguredStub(
            FilesDiffCommand::class,
            [
                'getFilesContent' =>
                [
                    "FILE1" => $this->file1Content,
                    "FILE2" => $this->file2Content
                ]
            ]
        );

        $this->assertInstanceOf(DisplayCommand::class, $this->displayCmd
                                                            ->execute($filesDiffCmd));
    }

    public function testGetDiffs()
    {
        $this->assertEquals("{\n}\n", $this->displayCmd->getDiffs());
    }
}