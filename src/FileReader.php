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

    public function readFile(string $filename, bool $isArray = null): ?array
    {
        if (file_exists($filename)) {
            $fileNameParts = explode(".", $this->normalizeFilename($filename));
            $fileFormat = end($fileNameParts);
            if ($fileFormat === 'json') {
                $handle = fopen($filename, "r");
                $result = json_decode(fread($handle, self::MAX_FILE_SIZE), $isArray);
                fclose($handle);
                $type = gettype($result);
                if ($type === 'object') {
                    return get_object_vars($result);
                } elseif ($type === 'array') {
                    return $result;
                }
            } else if ($fileFormat === 'yaml' || $fileFormat === 'yml') {
                $handle = fopen($filename, "r");
                $result = Yaml::parse(fread($handle, self::MAX_FILE_SIZE), Yaml::PARSE_OBJECT_FOR_MAP);
                fclose($handle);
                $type = gettype($result);
                if ($type === 'object') {
                    return get_object_vars($result);
                } elseif ($type === 'array') {
                    return $result;
                }
            } else {
                throw new \Exception("Unknown files format: \n" .
                                    "use .json, .yaml (.yml) enstead \n");
            }
        } else {
            return null;
        }
    }
}
