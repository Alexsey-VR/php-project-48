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

    private function constructContent($accum, $item): string
    {
        return $accum .= "\n    " . $this->normalizeData($item);
    }

    private function normalizeData($data): string
    {
        if (is_bool($data)) {
            if ($data) {
                return "true";
            } else {
                return "false";
            }
        } else {
            return $data;
        }
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

            $filename1Path = explode("/", $this->filesPaths[0]);
            $filename2Path = explode("/", $this->filesPaths[1]);

            $this->filesContent[] = end($filename1Path) . " content:\n";
            $this->filesContent[] = array_reduce(
                $file1Content,
                [$this, 'constructContent'],
                "{"
            ) . "\n}\n";

            $this->filesContent[] = end($filename2Path) . " content:\n";
            $this->filesContent[] = array_reduce(
                $file2Content,
                [$this, 'constructContent'],
                "{"
            ) . "\n}\n";

            $this->filesContentString = implode("", $this->filesContent);

            $file1Keys = array_keys($file1Content);
            $file2Keys = array_keys($file2Content);
            $mergedFileKeys = array_unique(array_merge($file1Keys, $file2Keys));
            $this->filesDiffs = array_map(
                function ($fileKey) use ($file1Content, $file2Content) {
                    if (array_key_exists($fileKey, $file2Content)) {
                        if (
                            array_key_exists($fileKey, $file1Content) &&
                            !strcmp($file1Content[$fileKey], $file2Content[$fileKey])
                        ) {
                            $result = "    " . $fileKey . ": " . $this->normalizeData($file1Content[$fileKey]) . "\n";
                        } elseif (array_key_exists($fileKey, $file1Content)) {
                            $result = "  - " . $fileKey . ": " . $this->normalizeData($file1Content[$fileKey]) . "\n" .
                                "  + " . $fileKey . ": " . $this->normalizeData($file2Content[$fileKey]) . "\n";
                        } else {
                            $result = "  + " . $fileKey . ": " . $this->normalizeData($file2Content[$fileKey]) . "\n";
                        }
                        return $result;
                    } else {
                        return "  - " . $fileKey . ": " . $this->normalizeData($file1Content[$fileKey]) . "\n";
                    }
                },
                $mergedFileKeys
            );
            $this->filesDiffsString = "{\n" . implode("", $this->filesDiffs) . "}\n";
        }

        return $this;
    }

    public function getFilesContent(): string
    {
        return $this->filesContentString;
    }

    public function getFilesDiffs(): string
    {
        return $this->filesDiffsString;
    }
}
