<?php

namespace Differ;

class DisplayCommand implements CommandInterface
{
    private array $filesDiffContent;
    private array $filesContent;

    public function __construct()
    {
        $this->filesContent = [];
        $this->filesDiffContent = [];

        return $this;
    }

    private function constructContent($accum, $item)
    {
        return $accum .= "\n    " . $item;
    }

    public function execute(object $data): object
    {

        $this->filesContent[] = "file1.json content:\n";
        $this->filesContent[] = array_reduce(
            $data->file1,
            [$this, 'constructContent'],
            "{"
        ) . "\r}\n";

        $this->filesContent[] = "file2.json content:\n";
        $this->filesContent[] = array_reduce(
            $data->file2,
            [$this, 'constructContent'],
            "{"
        ) . "\n}\n";

        $file1Array = $data->file1;
        $file1Keys = array_keys($file1Array);
        $file2Array = $data->file2;
        $this->filesDiffContent = array_map(
            function ($file1Key) use ($file1Array, $file2Array) {
                if (array_key_exists($file1Key, $file2Array)) {
                    if (!strcmp($file1Array[$file1Key], $file2Array[$file1Key])) {
                        return "    " . $file1Array[$file1Key] . "\n";
                    } else {
                        return "  - " . $file1Array[$file1Key] . "\n" .
                               "  + " . $file2Array[$file1Key] . "\n";
                    }
                } else {
                    return "  - " . $file1Array[$file1Key] . "\n";
                }
            },
            $file1Keys
        );
        return $this;
    }

    public function showContentToConsole()
    {
        echo implode("", $this->filesContent);

        return $this;
    }

    public function showDiffsToConsole()
    {
        echo "{\n" . implode("", $this->filesDiffContent) . "}\n";

        return $this;
    }
}
