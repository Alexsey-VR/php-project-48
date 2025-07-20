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
    }

    private function constructContent($accum, $item)
    {
        return $accum .= "\n    " . $item;
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        if (!is_null($command)) {
            $filesContent = $command->getFilesContent();

            $keys = array_keys($filesContent);
            $file1Content = $filesContent[$keys[0]];
            $file2Content = $filesContent[$keys[1]];

            $this->filesContent[] = "file1.json content:\n";
            $this->filesContent[] = array_reduce(
                $file1Content,
                [$this, 'constructContent'],
                "{"
            ) . "\r}\n";

            $this->filesContent[] = "file2.json content:\n";
            $this->filesContent[] = array_reduce(
                $file2Content,
                [$this, 'constructContent'],
                "{"
            ) . "\n}\n";

            $file1Keys = array_keys($file1Content);
            $this->filesDiffContent = array_map(
                function ($file1Key) use ($file1Content, $file2Content) {
                    if (array_key_exists($file1Key, $file2Content)) {
                        if (!strcmp($file1Content[$file1Key], $file2Content[$file1Key])) {
                            return "    " . $file1Content[$file1Key] . "\n";
                        } else {
                            return "  - " . $file1Content[$file1Key] . "\n" .
                                "  + " . $file2Content[$file1Key] . "\n";
                        }
                    } else {
                        return "  - " . $file1Content[$file1Key] . "\n";
                    }
                },
                $file1Keys
            );
        }
        return $this;
    }

    public function showContentToConsole()
    {
        echo implode("", $this->filesContent);
    }

    public function showDiffsToConsole()
    {
        echo "{\n" . implode("", $this->filesDiffContent) . "}\n";
    }

    public function getDiffs()
    {
        return "{\n" . implode("", $this->filesDiffContent) . "}\n";
    }
}
