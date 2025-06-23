<?php

namespace Differ;

class FilesDiffCommand implements CommandInterface
{
    private FileReaderInterface $fileReader;
    private array $filesData;
    private array $file1Content;
    private array $file2Content;
    private array $filesDiffContent;

    public function __construct()
    {
        $this->file1Content = [];
        $this->file2Content = [];
    }

    public function setFileReader(FileReaderInterface $fileReader): CommandInterface
    {
        $this->fileReader = $fileReader;

        return $this;
    }

    public function setFileNames(CommandInterface $command): CommandInterface
    {
        $this->filesData = $command->getFileNames();

        return $this;
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        $this->file1Content = $this->fileReader->readFile($this->filesData['FILE1']);
        $this->file2Content = $this->fileReader->readFile($this->filesData['FILE2']);

        return $this;
    }

    public function getFilesContent()
    {
        return
        [
            "FILE1" => $this->file1Content,
            "FILE2" => $this->file2Content
        ];
    }
}
