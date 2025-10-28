<?php

namespace Differ;

class FilesDiffCommand implements FilesDiffCommandInterface
{
    private FileReaderInterface $fileReader;
    private array $filesPaths;
    private array $filesDataItems;
    private array $content1Descriptor;
    private array $content2Descriptor;
    private array $differenceDescriptor;
    private const array STATUS_KEYS = [
        "not changed", "changed", "added", "deleted", "empty", "new value"
    ];

    public function __construct(FileReaderInterface $reader)
    {
        $this->filesDataItems = [];

        $this->fileReader = $reader;
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
                $history = ($contentDescriptor["history"] === "") ?
                    $fileKey : $contentDescriptor["history"] . "." . $fileKey;
                $fileContentKeys = array_keys(
                    is_array($fileContent) ? $fileContent : []
                );
                asort($fileContentKeys);
                $initContentDescriptor = [
                    "level" => $level,
                    "history" => $history,
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

        if ($bothFilesKeySet) {
            $status = ($file1Content === $file2Content) ?
                self::STATUS_KEYS[0] : self::STATUS_KEYS[1];
        } elseif ($file1KeyOnlySet) {
            $status = (($currentStatus === self::STATUS_KEYS[1]) && $nextItemIsNotArray) ?
                self::STATUS_KEYS[0] : self::STATUS_KEYS[3];
        } elseif ($file2KeyOnlySet) {
            $status = (($currentStatus === self::STATUS_KEYS[1]) && $nextItemIsNotArray) ?
                self::STATUS_KEYS[0] : self::STATUS_KEYS[2];
        }

        return $status;
    }

    private function getInitDifferenceDescriptor(
        $status,
        $level,
        $fileKey,
        $history,
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
        $initDifferenceDescriptor["history"] = $history;

        return $initDifferenceDescriptor;
    }

    /**
     * @return array<int,string>
     */
    public function getStatusKeys(): array
    {
        return self::STATUS_KEYS;
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

                $file1Content = $file1Item[$fileKey] ?? null;
                $file2Content = $file2Item[$fileKey] ?? null;
                $nextItemIsNotArray = !(is_array($file1Item) && is_array($file2Item));
                $currentStatus = $differenceDescriptor['status'];

                $status = $this->getNextItemStatus(
                    $file1Content,
                    $file2Content,
                    $currentStatus,
                    $nextItemIsNotArray
                );

                $level = $differenceDescriptor["level"] + 1;
                $history = ($differenceDescriptor["history"] === "") ?
                    $fileKey : $differenceDescriptor["history"] . "." . $fileKey;
                $contentKeys = array_keys(array_merge(
                    is_array($file1Content) ? $file1Content : [],
                    is_array($file2Content) ? $file2Content : []
                ));
                asort($contentKeys);

                $initDifferenceDescriptor = $this->getInitDifferenceDescriptor(
                    $status,
                    $level,
                    $fileKey,
                    $history,
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

    public function execute(CommandLineParserInterface $command): CommandInterface | FilesDiffCommandInterface
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
            asort($fileKeys);
            $initContent1Descriptor = [
                "level" => 0,
                "fileKey" => "initKey",
                "history" => "",
                "fileContent" => $file1Content
            ];

            $this->content1Descriptor = $this->getContent(
                $fileKeys,
                $initContent1Descriptor
            );

            $fileKeys = array_keys($file2Content);
            asort($fileKeys);
            $initContent2Descriptor = [
                "level" => 0,
                "fileKey" => "initKey",
                "history" => "",
                "fileContent" => $file2Content
            ];

            $this->content2Descriptor = $this->getContent(
                $fileKeys,
                $initContent2Descriptor
            );

            $mergedFileKeys = array_keys(array_merge($file1Content, $file2Content));
            asort($mergedFileKeys);
            $initDifferenceDescriptor = [
                "level" => 0,
                "status" => "init",
                "fileKey" => "initKey",
                "history" => "",
                "file1Content" => $file1Content,
                "file2Content" => $file2Content,
            ];
            $this->differenceDescriptor = $this->getDifference(
                $mergedFileKeys,
                $initDifferenceDescriptor
            );
        }

        return $this;
    }

    public function getFile1Name(): string
    {
        $filename1Path = explode("/", $this->filesPaths[0]);
        return end($filename1Path);
    }

    public function getFile2Name(): string
    {
        $filename2Path = explode("/", $this->filesPaths[1]);
        return end($filename2Path);
    }

    public function getContent1Descriptor(): array
    {
        return $this->content1Descriptor;
    }

    public function getContent2Descriptor(): array
    {
        return $this->content2Descriptor;
    }

    public function getDifferenceDescriptor(): array
    {
        return $this->differenceDescriptor;
    }
}
