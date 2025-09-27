<?php

namespace Differ;

use Symfony\Component\Yaml\Yaml;

class FileReader implements FileReaderInterface
{
    private const MAX_FILE_SIZE = 4096;
    private string $fileFormat;

    private function normalizeFilename(string $filename)
    {
        return strtolower($filename);
    }

    public function __construct(string $fileFormat = 'json')
    {
        $this->fileFormat = $fileFormat;
    }

    public function readFile(string $filename, bool $isArray = true): ?array
    {
        if (file_exists($filename)) {
            $fileNameParts = explode(".", $this->normalizeFilename($filename));
            $fileFormat = end($fileNameParts);
            if ($fileFormat === 'json') {
                $handle = fopen($filename, "r");
                $jsonVariables = json_decode(
                    fread($handle, self::MAX_FILE_SIZE),
                    $isArray
                );
                fclose($handle);                
                $type = gettype($jsonVariables);
                if ($type === 'object') {
                    $fileContentArray = get_object_vars($jsonVariables);
                } elseif ($type === 'array') {
                    $fileContentArray = $jsonVariables;
                }
            } elseif ($fileFormat === 'yaml' || $fileFormat === 'yml') {
                $fileContentArray = Yaml::parseFile($filename);
            } else {
                throw new DifferException("unknown files format: use json, yaml (yml) enstead\n");
            }

            return $fileContentArray;
        } else {
            return null;
        }
    }
}
