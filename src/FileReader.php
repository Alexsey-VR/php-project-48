<?php

namespace App;

use App\FileReaderInterface;

class FileReader implements FileReaderInterface
{
    private const MAX_FILE_SIZE = 4096;

    public function __construct()
    {
    }

    public function readFile(string $filename): array | null
    {
        if (file_exists($filename)) {
            $handle = fopen($filename, "r");
            $result = json_decode(fread($handle, self::MAX_FILE_SIZE));
            fclose($handle);
            $type = gettype($result);
            if ($type === 'object') {
                return (get_object_vars($result));
            } elseif ($type === 'array') {
                return $result;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
