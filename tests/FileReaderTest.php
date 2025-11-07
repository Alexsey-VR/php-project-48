<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(\Differ\FileReader::class)]
#[CoversMethod(\Differ\FileReader::class, 'readFile')]
class FileReaderTest extends TestCase
{
    public const JSON_FILE_FOR_ARRAY = __DIR__ . "/../fixtures/fileForArray.json";
    public function testReadFileAsObject()
    {
        $fileReader = new \Differ\FileReader();

        $fileContent = $fileReader->readFile(
            self::JSON_FILE_FOR_ARRAY
        );

        $this->assertJsonStringEqualsJsonFile(
            self::JSON_FILE_FOR_ARRAY,
            json_encode($fileContent)
        );
    }

    public function testReadFileAsArray()
    {
        $fileReader = new \Differ\FileReader();

        $fileContent = $fileReader->readFile(
            self::JSON_FILE_FOR_ARRAY,
            true
        );

        $this->assertJsonStringEqualsJsonFile(
            self::JSON_FILE_FOR_ARRAY,
            json_encode($fileContent)
        );
    }

    public function testReadFileNotJson()
    {
        $fileReader = new \Differ\FileReader();

        $fileContent = $fileReader->readFile(
            __DIR__ . "/../fixtures/fileNotJson.json"
        );

        $this->assertEquals([], $fileContent);
    }
}
