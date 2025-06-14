<?php

namespace App;

use App\CommandInterface;
use App\OutputInterface;
use App\FileReaderInterface;
use App\FileReader;

class ViewFilesCommand implements CommandInterface
{
    protected FileReaderInterface $fileReader;

    public function __construct(FileReaderInterface $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    public function execute(object $cliData)
    {
        if (isset($cliData['FILE1']) && isset($cliData['FILE2'])) {
            $file1Content = $this->fileReader->readFile($cliData['FILE1']);
            $file2Content = $this->fileReader->readFile($cliData['FILE2']);

            print_r("File1 content:\n");
            print_r($file1Content);
            print_r("File2 content:\n");
            print_r($file2Content);
        }
    }
}
