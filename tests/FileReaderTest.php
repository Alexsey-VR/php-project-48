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
    public const JSON_FILE_FOR_ARRAY = __DIR__ . "/../fixtures/fileForArray.json";
    public function testReadFileAsObject()
    {
        $fileReader = new FileReader();

        $fileContent = $fileReader->readFile(
            self::JSON_FILE_FOR_ARRAY);
        
        $this->assertJsonStringEqualsJsonFile(
            self::JSON_FILE_FOR_ARRAY,
            json_encode($fileContent));
    }

    public function testReadFileAsArray()
    {
        $fileReader = new FileReader();

        $fileContent = $fileReader->readFile(
            self::JSON_FILE_FOR_ARRAY,
            true
        );
        
        $this->assertJsonStringEqualsJsonFile(
            self::JSON_FILE_FOR_ARRAY,
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
