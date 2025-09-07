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

    private function stylish(array $differenceContent): array
    {
        return array_map(
            function ($differenceItem) {
                $result = null;
                $itemLevelShift = str_repeat("    ", $differenceItem["level"] - 1);
                $file1Item = is_array($differenceItem["file2Content"]) ?
                    $this->stylish($differenceItem["file1Content"]) : $differenceItem["file1Content"];
                $file2Item = is_array($differenceItem["file2Content"]) ?
                    $this->stylish($differenceItem["file2Content"]) : $differenceItem["file2Content"];

                if (!strcmp($differenceItem["status"], "not changed")) {
                    $result = $itemLevelShift . "    " . $differenceItem["fileKey"] . ": " . $file1Item;
                } elseif (!strcmp($differenceItem["status"], "changed")) {
                    $result = $itemLevelShift . "  - " . $differenceItem["fileKey"] . ": " . $file1Item . "\n" .
                        $itemLevelShift . "  + " . $differenceItem["fileKey"] . ": " . $file2Item;
                } elseif (!strcmp($differenceItem["status"], "added")) {
                    $result = $itemLevelShift . "  + " . $differenceItem["fileKey"] . ": " . $file2Item;
                } elseif (!strcmp($differenceItem["status"], "deleted")) {
                    $result = $itemLevelShift .
                    "  - " . $differenceItem["fileKey"] . ": " . $file1Item;
                }
                $result = $result . "\n";

                return $result;
            },
            $differenceContent
        );
    }

    private function getDifference($fileContentKeys, $file1Content, $file2Content): array
    {
        return array_reduce(
            $fileContentKeys,
            function ($contentDescriptor, $fileKey) {
                $file1Item = $contentDescriptor["file1Content"];
                $file2Item = $contentDescriptor["file2Content"];
                if (
                    array_key_exists($fileKey, $file2Item) &&
                    array_key_exists($fileKey, $file1Item)
                ) {
                    if ($file1Item[$fileKey] === $file2Item[$fileKey]) {
                        $contentDescriptor["result"][] = is_array($file1Item[$fileKey]) ?
                            getDifference(
                                array_keys(array_merge(
                                    $file1Item[$fileKey],
                                    $file2Item[$fileKey]
                                )),
                                $file1Item,
                                $file2Item
                            )
                            :
                            [
                                "level" => $contentDescriptor["level"] + 1,
                                "status" => "not changed",
                                "fileKey" => $fileKey,
                                "file1Content" => $file1Item[$fileKey],
                                "file2Content" => $file1Item[$fileKey]
                            ];
                    } else {
                        $contentDescriptor["result"][] = [
                            "level" => $contentDescriptor["level"] + 1,
                            "status" => "changed",
                            "fileKey" => $fileKey,
                            "file1Content" => $file1Item[$fileKey],
                            "file2Content" => $file2Item[$fileKey]
                        ];
                    }
                } elseif (
                    array_key_exists($fileKey, $file2Item) &&
                    !array_key_exists($fileKey, $file1Item)
                ) {
                    $contentDescriptor["result"][] = [
                        "level" => $contentDescriptor["level"] + 1,
                        "status" => "added",
                        "fileKey" => $fileKey,
                        "file1Content" => null,
                        "file2Content" => $file2Item[$fileKey]
                    ];
                } elseif (
                    !array_key_exists($fileKey, $file2Item) &&
                    array_key_exists($fileKey, $file1Item)
                ) {
                    $contentDescriptor["result"][] = [
                        "level" => $contentDescriptor["level"] + 1,
                        "status" => "deleted",
                        "fileKey" => $fileKey,
                        "file1Content" => $file1Item[$fileKey],
                        "file2Content" => null
                    ];
                }

                return $contentDescriptor;
            },
            [
                "level" => 0,
                "status" => null,
                "fileKey" => "init",
                "file1Content" => $file1Content,
                "file2Content" => $file2Content
            ]
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

            $mergedFileKeys = array_keys(array_merge($file1Content, $file2Content));
            $contentDescriptor = $this->getDifference(
                $mergedFileKeys,
                $file1Content,
                $file2Content
            );

            $this->filesDiffs = $this->stylish($contentDescriptor["result"]);

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
