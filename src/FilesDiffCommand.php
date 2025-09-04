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
        return $accum .= "\n    " . $item;
    }

    private function normalizeData($data)
    {
        $normalizedValue = $data;
        if (is_bool($data)) {
            if ($data) {
                $normalizedValue = "true";
            } else {
                $normalizedValue = "false";
            }
        } elseif (is_null($data)) {
            $normalizedValue = "null";
        } elseif (is_array($data)) {
            $normalizedValue = array_map(
                fn ($item) => $this->normalizeData($item),
                $data
            ); 
        }

        return $normalizedValue;
    }

    private function getDifference($contentListKeys, $file1Content, $file2Content): array
    {
        return array_map(
            function ($fileKey) use ($file1Content, $file2Content) {
                if (array_key_exists($fileKey, $file2Content)) {
                    if (
                        array_key_exists($fileKey, $file1Content) &&
                        !strcmp($file1Content[$fileKey], $file2Content[$fileKey])
                    ) {
                        $result = "    " . $fileKey . ": " . $file1Content[$fileKey] . "\n";
                    } elseif (array_key_exists($fileKey, $file1Content)) {
                        $result = "  - " . $fileKey . ": " . $file1Content[$fileKey] . "\n" .
                            "  + " . $fileKey . ": " . $file2Content[$fileKey] . "\n";
                    } else {
                        $result = "  + " . $fileKey . ": " . $file2Content[$fileKey] . "\n";
                    }
                    return $result;
                } else {
                    return "  - " . $fileKey . ": " . $file1Content[$fileKey] . "\n";
                }
            },
            $contentListKeys
        );
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

            $file1Content = array_map(
                fn ($item) => $this->normalizeData($item),
                $this->filesDataItems[0],
            );

            $file2Content = array_map(
                fn ($item) => $this->normalizeData($item),
                $this->filesDataItems[1]
            );

            $filename1Path = explode("/", $this->filesPaths[0]);
            $filename2Path = explode("/", $this->filesPaths[1]);

            $file1Name = end($filename1Path);
            $this->filesContent[] = $file1Name . " content:\n";
            $this->filesContent[] = array_reduce(
                $file1Content,
                [$this, 'constructContent'],
                "{"
            ) . "\n}\n";

            $file2Name = end($filename2Path);
            $this->filesContent[] = $file2Name . " content:\n";
            $this->filesContent[] = array_reduce(
                $file2Content,
                [$this, 'constructContent'],
                "{"
            ) . "\n}\n";

            $this->filesContentString = implode("", $this->filesContent);

            $file1Keys = array_keys($file1Content);
            $file2Keys = array_keys($file2Content);
            $mergedFileKeys = array_unique(array_merge($file1Keys, $file2Keys));
            $this->filesDiffs = $this->getDifference(
                $mergedFileKeys,
                $file1Content,
                $file2Content
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
