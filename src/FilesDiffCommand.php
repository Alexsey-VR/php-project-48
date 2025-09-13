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
    private const array STATUS_PREFIXES = [
        "not changed" => "    ",
        "changed" => " -+ ",
        "added" => "  + ",
        "deleted" => "  - "
    ];
    private const array STATUS_COMMENTS = [
        "not changed" => "",
        "changed" => [
            "deleted" => " # Старое значение",
            "added" => " # Новое значение"
        ],
        "deleted" => " # Удалена",
        "added" => " # Добавлена",
        "empty" => "# значения нет, но пробел после : есть"
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

                $bothFilesKeySet = isset($file1Content, $file2Content);
                $file1KeyOnlySet = isset($file1Content) && !isset($file2Content);
                $file2KeyOnlySet = !isset($file1Content) && isset($file2Content);

                $nextItemIsNotArray = !(is_array($file1Item) && is_array($file2Item));

                if ($bothFilesKeySet) {
                    if ($file1Content === $file2Content) {
                        $status = "not changed";
                    } else {
                        $status = "changed";
                    }
                } elseif ($file1KeyOnlySet) {
                    if (($currentStatus === "changed") && $nextItemIsNotArray) {
                        $status = "not changed";
                    } else {
                        $status = "deleted";
                    }
                } elseif ($file2KeyOnlySet) {
                    if (($currentStatus === "changed") && $nextItemIsNotArray) {
                        $status = "not changed";
                    } else {
                        $status = "added";
                    }
                }

                $level = $differenceDescriptor["level"] + 1;
                $contentKeys = array_keys(array_merge(
                    is_array($file1Content) ? $file1Content : [],
                    is_array($file2Content) ? $file2Content : []
                ));
                if ($status === "added") {
                    $initDifferenceDescriptor = [
                        "file1Content" => $file2Content,
                        "file2Content" => $file2Content,
                    ];
                } elseif ($status === "deleted") {
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
                $itemLevelShift = str_repeat(self::STATUS_PREFIXES["not changed"], $contentItem["level"]);

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
                $itemLevelShift = str_repeat(self::STATUS_PREFIXES["not changed"], $contentItem["level"] - 1);

                $statusComment = self::STATUS_COMMENTS[$contentItem["status"]];
                $emptyComment = self::STATUS_COMMENTS["empty"];

                if (isset($contentItem["output"])) {
                    $firstContentIsArray = is_array($contentItem["file1Content"]) &&
                        !is_array($contentItem["file2Content"]);
                    $secondContentIsArray = !is_array($contentItem["file1Content"]) &&
                        is_array($contentItem["file2Content"]);

                    if ($firstContentIsArray) {
                        $altDeleteComment = ($contentItem['file1Content'] === "") ?
                        $emptyComment  : "{$statusComment['deleted']}";

                        $altAddedComment = ($contentItem['file2Content'] === "") ?
                        $emptyComment  : "{$statusComment['added']}";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES["deleted"] .
                                    "{$contentItem['fileKey']}: ";
                        $result[] = "{" .
                                    self::STATUS_COMMENTS["changed"]["deleted"] .
                                    "\n" . implode($this->stylish($contentItem["output"])) .
                                    $itemLevelShift .
                                    self::STATUS_PREFIXES["not changed"] .
                                    "}\n";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES["added"] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file2Content']}" .
                                    "{$altAddedComment}" .
                                    "\n";
                    } elseif ($secondContentIsArray) {
                        $altDeleteComment = ($contentItem['file1Content'] === "") ?
                        $emptyComment  : "{$statusComment['deleted']}";

                        $altAddedComment = ($contentItem['file2Content'] === "") ?
                        $emptyComment  : "{$statusComment['added']}";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES["deleted"] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file1Content']}" .
                                    "{$altDeleteComment}" .
                                    "\n";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES["added"] .
                                    "{$contentItem['fileKey']}: ";
                        $result[] = "{" .
                                    self::STATUS_COMMENTS["changed"]["added"] .
                                    "\n" . implode($this->stylish($contentItem["output"])) .
                                    $itemLevelShift .
                                    self::STATUS_PREFIXES["not changed"] .
                                    "}\n";
                    } else {
                        $arrayStatusPrefix = (is_array($contentItem["output"]) &&
                            ($contentItem["status"] === "changed")) ?
                            self::STATUS_PREFIXES["not changed"] : self::STATUS_PREFIXES[$contentItem["status"]];

                        $altStatusComment = ($contentItem["status"] === "changed") ?
                            self::STATUS_COMMENTS["not changed"] : self::STATUS_COMMENTS[$contentItem["status"]];

                        $result[] = $itemLevelShift .
                                    $arrayStatusPrefix .
                                    "{$contentItem['fileKey']}";

                        $result[] = ": " . "{" .
                                    $altStatusComment .
                                    "\n" . implode($this->stylish($contentItem["output"])) .
                                    $itemLevelShift .
                                    self::STATUS_PREFIXES["not changed"] .
                                    "}\n";
                    }
                } else {
                    if ($contentItem["status"] === "changed") {
                        $altDeleteComment = ($contentItem['file1Content'] === "") ?
                        $emptyComment  : "{$statusComment['deleted']}";

                        $altAddedComment = ($contentItem['file2Content'] === "") ?
                        $emptyComment  : "{$statusComment['added']}";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES["deleted"] .
                                    "{$contentItem['fileKey']}: " .
                                    "{$contentItem['file1Content']}" .
                                    "{$altDeleteComment}" .
                                    "\n";

                        $result[] = $itemLevelShift .
                                    self::STATUS_PREFIXES["added"] .
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
