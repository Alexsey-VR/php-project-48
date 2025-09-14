<?php

namespace Differ;

class FilesDiffCommand implements CommandInterface
{
    private FileReaderInterface $fileReader;
    private array $filesPaths;
    private array $filesDataItems;
    private array $filesDiffs;
    private string $filesDiffsString;
    private array $files1Content;
    private string $files1ContentString;
    private array $files2Content;
    private string $files2ContentString;
    private string $filesContentString;
    private const array STATUS_KEYS = [
        "not changed", "changed", "added", "deleted", "empty"
    ];
    private const array STATUS_PREFIXES = [
        self::STATUS_KEYS[0] => "    ",
        self::STATUS_KEYS[1] => " -+ ",
        self::STATUS_KEYS[2] => "  + ",
        self::STATUS_KEYS[3] => "  - "
    ];
    private const array STATUS_COMMENTS = [
        self::STATUS_KEYS[0] => "",
        self::STATUS_KEYS[1] => [
            self::STATUS_KEYS[3] => " # Старое значение",
            self::STATUS_KEYS[2] => " # Новое значение"
        ],
        self::STATUS_KEYS[3] => " # Удалена",
        self::STATUS_KEYS[2] => " # Добавлена",
        self::STATUS_KEYS[4] => "# значения нет, но пробел после : есть"
    ];

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

    private function getContent(
        $fileContentKeys,
        $initContentDescriptor
    ): array {
        return array_reduce(
            $fileContentKeys,
            function ($contentDescriptor, $fileKey) {
                $fileItem = $contentDescriptor['fileContent'];

                $fileContent = $fileItem[$fileKey];

                $level = $contentDescriptor["level"] + 1;
                $fileContentKeys = array_keys(
                    is_array($fileContent) ? $fileContent : []
                );
                $initContentDescriptor = [
                    "level" => $level,
                    "fileKey" => $fileKey,
                    "fileContent" => $fileContent
                ];

                $contentDescriptor['output'][] = $this->getContent(
                    $fileContentKeys,
                    $initContentDescriptor
                );

                return $contentDescriptor;
            },
            $initContentDescriptor
        );
    }

    private function getNextItemStatus(
        $file1Content,
        $file2Content,
        $currentStatus,
        $nextItemIsNotArray
    ): string {
        $status = self::STATUS_KEYS[0];
        $bothFilesKeySet = isset($file1Content, $file2Content);
        $file1KeyOnlySet = isset($file1Content) && !isset($file2Content);
        $file2KeyOnlySet = !isset($file1Content) && isset($file2Content);

        if (isset($file1Content, $file2Content)) {
            $status = ($file1Content === $file2Content) ?
                self::STATUS_KEYS[0] : self::STATUS_KEYS[1];
        } elseif (isset($file1Content) && !isset($file2Content)) {
            $status = (($currentStatus === self::STATUS_KEYS[1]) && $nextItemIsNotArray) ?
                self::STATUS_KEYS[0] : self::STATUS_KEYS[3];
        } elseif (!isset($file1Content) && isset($file2Content)) {
            $status = (($currentStatus === self::STATUS_KEYS[1]) && $nextItemIsNotArray) ?
                self::STATUS_KEYS[0] : self::STATUS_KEYS[2];
        }

        return $status;
    }

    private function getInitDifferenceDescriptor(
        $status,
        $level,
        $fileKey,
        $file1Content,
        $file2Content
    ): array {
        if ($status === self::STATUS_KEYS[2]) {
            $initDifferenceDescriptor = [
                "file1Content" => $file2Content,
                "file2Content" => $file2Content,
            ];
        } elseif ($status === self::STATUS_KEYS[3]) {
            $initDifferenceDescriptor = [
                "file1Content" => $file1Content,
                "file2Content" => $file1Content,
            ];
        } else {
            $initDifferenceDescriptor = [
                "file1Content" => $file1Content,
                "file2Content" => $file2Content,
            ];
        }
        $initDifferenceDescriptor["level"] = $level;
        $initDifferenceDescriptor["status"] = $status;
        $initDifferenceDescriptor["fileKey"] = $fileKey;

        return $initDifferenceDescriptor;
    }

    private function getDifference(
        $fileContentKeys,
        $initDifferenceDescriptor
    ): array {
        return array_reduce(
            $fileContentKeys,
            function ($differenceDescriptor, $fileKey) {
                $file1Item = $differenceDescriptor["file1Content"];
                $file2Item = $differenceDescriptor["file2Content"];

                $currentStatus = $differenceDescriptor['status'];
                $file1Content = $file1Item[$fileKey] ?? null;
                $file2Content = $file2Item[$fileKey] ?? null;
                $nextItemIsNotArray = !(is_array($file1Item) && is_array($file2Item));

                $status = $this->getNextItemStatus(
                    $file1Content,
                    $file2Content,
                    $currentStatus,
                    $nextItemIsNotArray
                );

                $level = $differenceDescriptor["level"] + 1;
                $contentKeys = array_keys(array_merge(
                    is_array($file1Content) ? $file1Content : [],
                    is_array($file2Content) ? $file2Content : []
                ));

                $initDifferenceDescriptor = $this->getInitDifferenceDescriptor(
                    $status,
                    $level,
                    $fileKey,
                    $file1Content,
                    $file2Content
                );
                
                $differenceDescriptor["output"][] = $this->getDifference(
                    $contentKeys,
                    $initDifferenceDescriptor
                );
                return $differenceDescriptor;
            },
            $initDifferenceDescriptor
        );
    }

    private function stylishContent(array $content): array
    {
        return array_reduce(
            $content,
            function ($result, $contentItem) {
                $itemLevelShift = str_repeat(self::STATUS_PREFIXES[self::STATUS_KEYS[0]], $contentItem["level"]);

                if (isset($contentItem["output"])) {
                    $result[] = $itemLevelShift .
                                "{$contentItem['fileKey']}: ";
                    $result[] = "{" .
                                "\n" . implode($this->stylishContent($contentItem["output"])) .
                                $itemLevelShift .
                                "}\n";
                } else {
                    $result[] = $itemLevelShift .
                                "{$contentItem['fileKey']}: " .
                                $contentItem["fileContent"] .
                                "\n";
                }

                return $result;
            },
            []
        );
    }

    private function stylish(array $content): array
    {
        return array_reduce(
            $content,
            function ($result, $contentItem) {
                $itemLevelShift = str_repeat(self::STATUS_PREFIXES[self::STATUS_KEYS[0]], $contentItem["level"] - 1);

                $statusComment = self::STATUS_COMMENTS[$contentItem["status"]];
                $emptyComment = self::STATUS_COMMENTS[self::STATUS_KEYS[4]];

                if (isset($contentItem["output"])) {
                    $firstContentIsArray = is_array($contentItem["file1Content"]) &&
                        !is_array($contentItem["file2Content"]);
                    $secondContentIsArray = !is_array($contentItem["file1Content"]) &&
                        is_array($contentItem["file2Content"]);

                    if ($firstContentIsArray) {
                        $altDeleteComment = ($contentItem['file1Content'] === "") ?
                        $emptyComment  : "{$statusComment[self::STATUS_KEYS[3]]}";

                        $altAddedComment = ($contentItem['file2Content'] === "") ?
                        $emptyComment  : "{$statusComment[self::STATUS_KEYS[2]]}";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES["deleted"] .
                                    "{$contentItem['fileKey']}: ";
                        $result[] = "{" .
                                    self::STATUS_COMMENTS[self::STATUS_KEYS[1]]["deleted"] .
                                    "\n" . implode($this->stylish($contentItem["output"])) .
                                    $itemLevelShift .
                                    self::STATUS_PREFIXES[self::STATUS_KEYS[0]] .
                                    "}\n";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES[self::STATUS_KEYS[2]] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file2Content']}" .
                                    "{$altAddedComment}" .
                                    "\n";
                    } elseif ($secondContentIsArray) {
                        $altDeleteComment = ($contentItem['file1Content'] === "") ?
                        $emptyComment  : "{$statusComment[self::STATUS_KEYS[3]]}";

                        $altAddedComment = ($contentItem['file2Content'] === "") ?
                        $emptyComment  : "{$statusComment[self::STATUS_KEYS[2]]}";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES[self::STATUS_KEYS[3]] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file1Content']}" .
                                    "{$altDeleteComment}" .
                                    "\n";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES[self::STATUS_KEYS[2]] .
                                    "{$contentItem['fileKey']}: ";
                        $result[] = "{" .
                                    self::STATUS_COMMENTS[self::STATUS_KEYS[1]][self::STATUS_KEYS[2]] .
                                    "\n" . implode($this->stylish($contentItem["output"])) .
                                    $itemLevelShift .
                                    self::STATUS_PREFIXES[self::STATUS_KEYS[0]] .
                                    "}\n";
                    } else {
                        $arrayStatusPrefix = (is_array($contentItem["output"]) &&
                            ($contentItem["status"] === self::STATUS_KEYS[1])) ?
                            self::STATUS_PREFIXES[self::STATUS_KEYS[0]] : self::STATUS_PREFIXES[$contentItem["status"]];

                        $altStatusComment = ($contentItem["status"] === self::STATUS_KEYS[1]) ?
                            self::STATUS_COMMENTS[self::STATUS_KEYS[0]] : self::STATUS_COMMENTS[$contentItem["status"]];

                        $result[] = $itemLevelShift .
                                    $arrayStatusPrefix .
                                    "{$contentItem['fileKey']}";

                        $result[] = ": " . "{" .
                                    $altStatusComment .
                                    "\n" . implode($this->stylish($contentItem["output"])) .
                                    $itemLevelShift .
                                    self::STATUS_PREFIXES[self::STATUS_KEYS[0]] .
                                    "}\n";
                    }
                } else {
                    if ($contentItem["status"] === self::STATUS_KEYS[1]) {
                        $altDeleteComment = ($contentItem['file1Content'] === "") ?
                        $emptyComment  : "{$statusComment[self::STATUS_KEYS[3]]}";

                        $altAddedComment = ($contentItem['file2Content'] === "") ?
                        $emptyComment  : "{$statusComment[self::STATUS_KEYS[2]]}";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES[self::STATUS_KEYS[3]] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file1Content']}" .
                                    "{$altDeleteComment}" .
                                    "\n";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES[self::STATUS_KEYS[2]] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file2Content']}" .
                                    "{$altAddedComment}" .
                                    "\n";
                    } elseif (isset($contentItem['file1Content'])) {
                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES[$contentItem["status"]] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file1Content']}" .
                                    self::STATUS_COMMENTS[$contentItem["status"]] .
                                    "\n";
                    } else {
                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES[$contentItem["status"]] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file2Content']}" .
                                    self::STATUS_COMMENTS[$contentItem["status"]] .
                                    "\n";
                    }
                }

                return $result;
            },
            []
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
                fn($item) => $this->normalizeData($item),
                $this->filesDataItems[0],
            );

            $file2Content = array_map(
                fn($item) => $this->normalizeData($item),
                $this->filesDataItems[1]
            );

            $fileKeys = array_keys($file1Content);
            $initContent1Descriptor = [
                "level" => 0,
                "fileKey" => "initKey",
                "fileContent" => $file1Content
            ];
            $content1Descriptor = $this->getContent(
                $fileKeys,
                $initContent1Descriptor
            );

            $fileKeys = array_keys($file2Content);
            $initContent2Descriptor = [
                "level" => 0,
                "fileKey" => "initKey",
                "fileContent" => $file2Content
            ];
            $content2Descriptor = $this->getContent(
                $fileKeys,
                $initContent2Descriptor
            );

            $filename1Path = explode("/", $this->filesPaths[0]);
            $filename2Path = explode("/", $this->filesPaths[1]);
            $file1Name = end($filename1Path);
            $file2Name = end($filename2Path);

            $this->files1Content = $this->stylishContent($content1Descriptor["output"]);
            $this->files1ContentString = "File {$file1Name} content:\n" .
                "{\n" . implode("", $this->files1Content) . "}\n";

            $this->files2Content = $this->stylishContent($content2Descriptor["output"]);
            $this->files2ContentString = "File {$file2Name} content:\n" .
                "{\n" . implode("", $this->files2Content) . "}\n";

            $this->filesContentString = $this->files1ContentString .
                $this->files2ContentString;

            $mergedFileKeys = array_keys(array_merge($file1Content, $file2Content));
            $initDifferenceDescriptor = [
                "level" => 0,
                "status" => "init",
                "fileKey" => "initKey",
                "file1Content" => $file1Content,
                "file2Content" => $file2Content,
            ];
            $differenceDescriptor = $this->getDifference(
                $mergedFileKeys,
                $initDifferenceDescriptor
            );

            $this->filesDiffs = $this->stylish($differenceDescriptor["output"]);
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
