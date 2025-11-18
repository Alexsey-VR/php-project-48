<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;
use Differ\Exceptions\DifferException;
use Differ\Interfaces\FileParserInterface;
use Differ\Interfaces\FileReaderInterface;

class FileParser implements FileParserInterface
{
    private string $fileName;
    private string $fileFormat;
    private string $fileContent;

    public function __construct()
    {
        $this->fileName = "";
        $this->fileFormat = "";
        $this->fileContent = "";
    }

    public function execute(FileReaderInterface $fileReader, bool $isArray = true): array
    {
        $this->fileName = $fileReader->getName();
        $this->fileFormat = $fileReader->getFormat();
        $this->fileContent = $fileReader->getContent();

        $fileContentArray = [];
        if ($this->fileFormat === 'json') {
            $jsonVariables = json_decode(
                $this->fileContent,
                $isArray
            );

            $fileContentArray = is_array($jsonVariables) ?
                $jsonVariables : throw new DifferException("internal error: json file can't be parsed\n");
        } elseif ($this->fileFormat === 'yaml' || $this->fileFormat === 'yml') {
            $yamlVariables = Yaml::parseFile($this->fileName);
            $fileContentArray = is_array($yamlVariables) ?
            $yamlVariables : throw new DifferException("internal error: yaml file can't be parsed\n");
        }

        return $fileContentArray;
    }
}
