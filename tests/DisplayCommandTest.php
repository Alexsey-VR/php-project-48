<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\DisplayCommand;

#[CoversClass(DisplayCommand::class)]
#[CoversMethod(DisplayCommand::class, 'execute')]
class DisplayCommandTest extends TestCase
{
    private $filesContent;
    private $filesDiffs;
    private $filesDiffCmd;

    protected function setUp(): void
    {
        $this->filesContent = "file 1 content:\n" .
            "{\n" .
            "    'id': 'none',\n" .
            "    'host': 'hexlet.io',\n" .
            "    'timeout': 50\n" .
            "}\n" .
            "file 2 content:\n" .
            "{\n" .
            "    'timeout': 20,\n" .
            "    'verbose': 1,\n" .
            "    'host': 'hexlet.io'\n" .
            "}\n";

        $this->filesDiffs = "{\n" .
            " +  'id': 'none',\n" .
            "    'host': 'hexlet.io',\n" .
            " -  'timeout': 50,\n" .
            " +  'timeout': 20,\n" .
            " +  'verbose': 1\n" .
            "}\n";

        $this->filesDiffCmd = $this->createConfiguredStub(
            FilesDiffCommand::class,
            [
                'getFilesContent' => $this->filesContent,
                'getFilesDiffs' => $this->filesDiffs,
            ]
        );
    }

    public function testInstance()
    {
        $displayCmd = new DisplayCommand();

        $this->assertInstanceOf(DisplayCommand::class, $displayCmd);
    }

    public function testFilesDiffs()
    {
        $displayCmd = new DisplayCommand();

        $displayCmd->execute($this->filesDiffCmd);
        $this->expectOutputString($this->filesDiffs);
    }

    public function testFilesContent()
    {
        $displayCmd = new DisplayCommand();

        $displayCmd->setMode("content")->execute($this->filesDiffCmd);
        $this->expectOutputString($this->filesContent);
    }

    public function testUnknownDisplayMode()
    {
        $displayCmd = new DisplayCommand();

        $displayCmd->setMode("extra")->execute($this->filesDiffCmd);
        $this->expectOutputString("error: unknown mode");
    }
}
