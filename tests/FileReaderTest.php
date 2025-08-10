<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\FileReader;

#[CoversClass(FileReader::class)]
#[CoversMethod(FileReader::class, 'readFile')]
class FileReaderTest extends TestCase
{
    public function testReadFileAsObject()
    {
        $fileReader = new FileReader();

        $fileContent = $fileReader->readFile(
            __DIR__ . "/../fixtures/fileForArray.json");
        
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/../fixtures/fileForArray.json",
            json_encode($fileContent));
    }

    public function testReadFileAsArray()
    {
        $fileReader = new FileReader();

        $fileContent = $fileReader->readFile(
            __DIR__ . "/../fixtures/fileForArray.json",
            true
        );
        
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/../fixtures/fileForArray.json",
            json_encode($fileContent));
    }

    public function testReadFileNotJson()
    {
        $fileReader = new FileReader();

        $fileContent = $fileReader->readFile(
            __DIR__ . "/../fixtures/fileNotJson.json"
        );
        
        $this->assertNull($fileContent);
    }
}