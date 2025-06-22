<?php

namespace Differ;

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

    public function setFileReader(FileReaderInterface $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    public function execute(array $cliData): ?array
    {
        if (isset($cliData['FILE1']) && isset($cliData['FILE2'])) {
            $this->file1Content = $this->fileReader->readFile($cliData['FILE1']);
            $this->file2Content = $this->fileReader->readFile($cliData['FILE2']);
        }

        return
        [
            'file1' => $this->file1Content,
            'file2' => $this->file2Content
        ];
    }
}
