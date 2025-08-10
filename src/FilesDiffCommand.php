<?php

namespace Differ;

class FilesDiffCommand implements CommandInterface
{
    private FileReaderInterface $fileReader;
    private array $filesPaths;
    private array $filesDataItems;
    private array $filesDiffs;
    private string $filesDiffsString;
    private array $filesContent;
    private string $filesContentString;

    private function constructContent($accum, $item)
    {
        return $accum .= "\n    " . $item;
    }

    public function __construct()
    {
        $this->filesDataItems = [];
    }

    public function setFileReader(FileReaderInterface $fileReader): CommandInterface
    {
        $this->fileReader = $fileReader;

        return $this;
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        if (!is_null($command)) {
            $this->filesPaths = [
                $command->getFileNames()['FILE1'],
                $command->getFileNames()['FILE2']
            ];

            foreach ($this->filesPaths as $filePath) {
                $this->filesDataItems[] = $this->fileReader->readFile($filePath);
            }

            $file1Content = $this->filesDataItems[0];
            $file2Content = $this->filesDataItems[1];

            $this->filesContent[] = "file1.json content:\n";
            $this->filesContent[] = array_reduce(
                $file1Content,
                [$this, 'constructContent'],
                "{"
            ) . "\n}\n";

            $this->filesContent[] = "file2.json content:\n";
            $this->filesContent[] = array_reduce(
                $file2Content,
                [$this, 'constructContent'],
                "{"
            ) . "\n}\n";

            $this->filesContentString = implode("", $this->filesContent);

            $file1Keys = array_keys($file1Content);
            $this->filesDiffs = array_map(
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
            $this->filesDiffsString = "{\n" . implode("", $this->filesDiffs) . "}\n";
        }

        return $this;
    }

    public function getFilesContent()
    {
        return $this->filesContentString;
    }

    public function getFilesDiffs()
    {
        return $this->filesDiffsString;
    }
}
