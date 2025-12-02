<?php

namespace Differ\Readers;

use Differ\Exceptions\DifferException;
use Differ\Interfaces\FileReaderInterface;

class FileReader implements FileReaderInterface
{
    private const MAX_FILE_SIZE = 4096;
    private string $fileName;
    private string $fileFormat;
    private string $fileContent;

    public function __construct()
    {
        $this->fileName = "";
        $this->fileFormat = "";
        $this->fileContent = "";
    }

    private function normalizeFilename(string $fileName): string
    {
        return strtolower($fileName);
    }

    public function readFile(string $fileName): FileReaderInterface
    {
        $this->fileName = __DIR__;
        $fileExists = file_exists($fileName);
        if ($fileExists) {
            $fileNameParts = explode(".", $this->normalizeFilename($fileName));
            $this->fileName = $fileName;
            $this->fileFormat = end($fileNameParts);
        } else {
            throw new DifferException("input error: file {$fileName} is not exists.\n");
        }

        $this->fileContent = "";
        if (($handle = fopen($this->fileName, "r")) !== false) {
            $fileData = fread($handle, self::MAX_FILE_SIZE);
            $this->fileContent = ($fileData !== false) ? $fileData : "";
            fclose($handle);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->fileName;
    }

    public function getFormat(): string
    {
        return $this->fileFormat;
    }

    public function getContent(): string
    {
        return $this->fileContent;
    }
}
