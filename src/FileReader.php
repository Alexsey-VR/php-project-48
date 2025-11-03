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
    public function readFile(string $filename, bool $isArray = true): array
    {
        $fileContentArray = [];
        if (file_exists($filename)) {
            $fileNameParts = explode(".", $this->normalizeFilename($filename));
            $fileFormat = end($fileNameParts);
            if ($fileFormat === 'json') {
                $handle = fopen($filename, "r");
                $jsonVariables = [];
                if ($handle !== false) {
                    $fileData = fread($handle, self::MAX_FILE_SIZE);
                    if ($fileData !== false) {
                        $jsonVariables = json_decode(
                            $fileData,
                            $isArray
                        );
                    }
                    fclose($handle);
                }
                $type = gettype($jsonVariables);
                $fileContentArray = is_array($jsonVariables) ?
                $jsonVariables : throw new DifferException("internal error: json file can't be parsed\n");
            } elseif ($fileFormat === 'yaml' || $fileFormat === 'yml') {
                $yamlVariables = Yaml::parseFile($filename);
                $fileContentArray = is_array($yamlVariables) ?
                $yamlVariables : throw new DifferException("internal error: yaml file can't be parsed\n");
            } else {
                throw new DifferException("unknown files format: use json, yaml (yml) enstead\n");
            }
        }
        return $fileContentArray;
    }
}
