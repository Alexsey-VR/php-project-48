<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\Readers\FileReader;

#[CoversClass(FileReader::class)]
#[CoversClass(\Differ\Parsers\FileParser::class)]
#[CoversMethod(FileReader::class, 'readFile')]
class FileParserTest extends TestCase
{
    public function testReadFileAsObject()
    {
        $jsonFileForArray = $_ENV['FIXTURES_PATH'] . "/fileForArray.json";

        $fileReader = new FileReader();
        $fileParser = new \Differ\Parsers\FileParser();

        $fileReaderContainer = $fileReader->readFile(
            $jsonFileForArray
        );

        $this->assertStringEqualsFile(
            $jsonFileForArray,
            $fileReaderContainer->getContent()
        );

        $this->assertEquals(
            "json",
            $fileReaderContainer->getFormat()
        );

        $this->assertEquals(
            $jsonFileForArray,
            $fileReaderContainer->getName()
        );

        $fileContent = $fileParser->execute($fileReaderContainer);

        $this->assertJsonStringEqualsJsonFile(
            $jsonFileForArray,
            json_encode($fileContent)
        );
    }

    public function testReadFileAsArray()
    {
        $jsonFileForArray = $_ENV['FIXTURES_PATH'] . "/fileForArray.json";

        $fileReader = new FileReader();
        $fileParser = new \Differ\Parsers\FileParser();

        $fileContent = $fileParser->execute(
            $fileReader->readFile(
                $jsonFileForArray,
                true
            )
        );

        $this->assertJsonStringEqualsJsonFile(
            $jsonFileForArray,
            json_encode($fileContent)
        );
    }

    public function testReadFileNotJson()
    {
        $fileReader = new FileReader();
        $fileParser = new \Differ\Parsers\FileParser();

        $fileContent = $fileParser->execute(
            $fileReader->readFile(
                $_ENV['FIXTURES_PATH'] . "/fileNotJson.json"
            )
        );

        $this->assertEquals([], $fileContent);
    }
}
