<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(\Differ\FileReader::class)]
#[CoversClass(\Differ\FileParser::class)]
#[CoversMethod(\Differ\FileReader::class, 'readFile')]
class FileParserTest extends TestCase
{
    public const JSON_FILE_FOR_ARRAY = __DIR__ . "/../fixtures/fileForArray.json";
    public function testReadFileAsObject()
    {
        $fileReader = new \Differ\FileReader();
        $fileParser = new \Differ\FileParser();

        $fileReaderContainer = $fileReader->readFile(
            self::JSON_FILE_FOR_ARRAY
        );

        $this->assertStringEqualsFile(
            self::JSON_FILE_FOR_ARRAY,
            $fileReaderContainer->getContent()
        );

        $this->assertEquals(
            "json",
            $fileReaderContainer->getFormat()
        );

        $this->assertEquals(
            self::JSON_FILE_FOR_ARRAY,
            $fileReaderContainer->getName()
        );

        $fileContent = $fileParser->execute($fileReaderContainer);

        $this->assertJsonStringEqualsJsonFile(
            self::JSON_FILE_FOR_ARRAY,
            json_encode($fileContent)
        );
    }

    public function testReadFileAsArray()
    {
        $fileReader = new \Differ\FileReader();
        $fileParser = new \Differ\FileParser();

        $fileContent = $fileParser->execute(
            $fileReader->readFile(
                self::JSON_FILE_FOR_ARRAY,
                true
            )
        );

        $this->assertJsonStringEqualsJsonFile(
            self::JSON_FILE_FOR_ARRAY,
            json_encode($fileContent)
        );
    }

    public function testReadFileNotJson()
    {
        $fileReader = new \Differ\FileReader();
        $fileParser = new \Differ\FileParser();

        $fileContent = $fileParser->execute(
            $fileReader->readFile(
                __DIR__ . "/../fixtures/fileNotJson.json"
            )
        );

        $this->assertEquals([], $fileContent);
    }
}
