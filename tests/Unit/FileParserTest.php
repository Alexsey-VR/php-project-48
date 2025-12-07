<?php

namespace Differ\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Differ\Readers\FileReader;
use Differ\Parsers\FileParser;
use Differ\Exceptions\DifferException;
use Differ\Tests\Fixtures\FixturesHelper;

#[CoversClass(FileReader::class)]
#[CoversClass(FileParser::class)]
#[CoversMethod(FileReader::class, 'readFile')]
#[CoversClass(DifferException::class)]
class FileParserTest extends TestCase
{
    public function testReadFileAsObject()
    {
        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        $jsonFileForArray = "{$fullFixturesPath}/fileForArray.json";

        $fileReader = new FileReader();
        $fileParser = new FileParser();

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
        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        $jsonFileForArray = "{$fullFixturesPath}/fileForArray.json";

        $fileReader = new FileReader();
        $fileParser = new FileParser();

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
        $fileParser = new FileParser();

        $this->expectException(DifferException::class);

        $fixturesHelper = new FixturesHelper();
        $fullFixturesPath = $fixturesHelper->getFullFixturesPath();
        $fileContent = $fileParser->execute(
            $fileReader->readFile(
                "{$fullFixturesPath}/fileNotJSON.json"
            )
        );

        $this->assertEquals([], $fileContent);
    }
}
