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

        return $this;
    }

    public function setFileReader(FileReaderInterface $fileReader): object
    {
        $this->fileReader = $fileReader;

        return $this;
    }

    public function execute(object $cliData): object
    {
        if (isset($cliData->args['FILE1']) && isset($cliData->args['FILE2'])) {
            $this->file1Content = $this->fileReader->readFile($cliData->args['FILE1']);
            $this->file2Content = $this->fileReader->readFile($cliData->args['FILE2']);
        }

        return
        (object)[
            'file1' => $this->file1Content,
            'file2' => $this->file2Content
        ];
    }
}
