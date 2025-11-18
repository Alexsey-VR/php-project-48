<?php

namespace Differ\Readers;

use Differ\Exceptions\DifferException;

class FileReader implements \Differ\Interfaces\FileReaderInterface
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

    public function readFile(string $fileName): \Differ\Interfaces\FileReaderInterface
    {
        $this->fileContent = "";
        if (file_exists($fileName)) {
            $fileNameParts = explode(".", $this->normalizeFilename($fileName));
            $this->fileName = $fileName;
            $this->fileFormat = end($fileNameParts);
            if ($this->fileFormat === 'json') {
                $handle = fopen($this->fileName, "r");
                if ($handle !== false) {
                    $fileData = fread($handle, self::MAX_FILE_SIZE);
                    if ($fileData !== false) {
                        $this->fileContent = $fileData;
                    }
                    fclose($handle);
                }
            } elseif (($this->fileFormat === "yaml" || $this->fileFormat === "yml") === false) {
                throw new DifferException("unknown files format: use json, yaml (yml) enstead\n");
            }
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
