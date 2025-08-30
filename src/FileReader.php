<?php

namespace Differ;

class FileReader implements FileReaderInterface
{
    private const MAX_FILE_SIZE = 4096;
    private string $fileFormat;

    public function __construct(string $fileFormat = 'json')
    {
        $this->fileFormat = $fileFormat;
    }

    public function readFile(string $filename, bool $isArray = null): ?array
    {
        if (file_exists($filename)) {
            $handle = fopen($filename, "r");
            $result = json_decode(fread($handle, self::MAX_FILE_SIZE), $isArray);
            fclose($handle);
            $type = gettype($result);
            if ($type === 'object') {
                return get_object_vars($result);
            } elseif ($type === 'array') {
                return $result;
            }
        } else {
            return null;
        }
    }
}
