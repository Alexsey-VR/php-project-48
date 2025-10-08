<?php

namespace Differ;

use Symfony\Component\Yaml\Yaml;

class FileReader implements FileReaderInterface
{
    private const MAX_FILE_SIZE = 4096;

    private function normalizeFilename(string $filename): string
    {
        return strtolower($filename);
    }

    /**
     * @return array<mixed,mixed>
     */
    public function readFile(string $filename): array
    {
        $fileContentArray = [];
        $fileNameParts = explode(".", $this->normalizeFilename($filename));
        $fileFormat = end($fileNameParts);
        if ($fileFormat === 'json') {
            $handle = fopen($filename, "r");
            $fileData = fread(
                ($handle !== false) ? $handle : throw new DifferException("internal error: can't open file\n"),
                self::MAX_FILE_SIZE
            );
            $jsonVariables = json_decode(
                ($fileData !== false) ? $fileData : "",
                flags: JSON_OBJECT_AS_ARRAY
            );
            fclose($handle);
            $fileContentArray = is_array($jsonVariables) ? $jsonVariables
            : throw new DifferException("internal error: json file can't be parsed\n");
        } elseif ($fileFormat === 'yaml' || $fileFormat === 'yml') {
            $yamlVariables = Yaml::parseFile($filename);
            $fileContentArray = is_array($yamlVariables) ? $yamlVariables
            : throw new DifferException("internal error: yaml file can't be parsed\n");
        } else {
            throw new DifferException("unknown files format: use json, yaml (yml) enstead\n");
        }

        return $fileContentArray;
    }
}
