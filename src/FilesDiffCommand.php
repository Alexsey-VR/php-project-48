<?php

namespace App;

use App\CommandInterface;
use App\FileReaderInterface;
use App\FileReader;

class FilesDiffCommand implements CommandInterface
{
    private FileReaderInterface $fileReader;
    private array $file1Content;
    private array $file2Content;
    private array $filesDiffContent;

    public function __construct()
    {
        $this->file1Content = [];
        $this->file2Content = [];
    }

    public function setFileReader(FileReaderInterface $fileReader): object
    {
        $this->fileReader = $fileReader;

        return $this;
    }

    public function execute(object $cliData): object
    {
        if (isset($cliData['FILE1']) && isset($cliData['FILE2'])) {
            $this->file1Content = $this->fileReader->readFile($cliData['FILE1']);
            $this->file2Content = $this->fileReader->readFile($cliData['FILE2']);
        }

        return
        (object)[
            'file1' => $this->file1Content,
            'file2' => $this->file2Content
        ];
    }
}
