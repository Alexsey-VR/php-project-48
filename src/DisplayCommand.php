<?php

namespace App;

use App\CommandInterface;

class DisplayCommand implements CommandInterface
{
    private array $filesDiffContent;

    public function __construct()
    {
        $this->filesDiffContent = [];
    }

    private function constructContent($accum, $item)
    {
        return $accum .= "\n    " . $item;
    }

    public function execute(object $data): object
    {

        $this->filesDiffContent[] = "file1.json content:\n";
        $this->filesDiffContent[] = array_reduce(
            get_object_vars($data)['file1'],
            [$this, 'constructContent'],
            "{") . "\r}\n";

        $this->filesDiffContent[] = "file2.json content:\n";
        $this->filesDiffContent[] = array_reduce(
            get_object_vars($data)['file2'],
            [$this, 'constructContent'],
            "{") . "\n}\n";

        return $this;
    }

    public function showDiffsToConsole()
    {
        echo implode("", $this->filesDiffContent);
    }
}
