<?php

namespace Differ\Differ;

use Differ\Interfaces\FilesDiffCommandInterface as FDCI;
use Differ\Interfaces\CommandLineParserInterface as CLPI;
use Differ\Interfaces\FileParserInterface as FPI;
use Differ\Exceptions\DifferException;
use Differ\Interfaces\FileReaderInterface;

class FilesDiffCommand implements FDCI
{
    private FileReaderInterface $fileReader;

    /**
     * @var array<int,string> $filesPaths
     */
    private array $filesPaths;

    /**
     * @var array<mixed,mixed> $filesDataItems
     */
    private array $filesDataItems;

    /**
     * @var array<mixed,mixed> $content1Descriptor
     */
    private array $content1Descriptor;

    /**
     * @var array<mixed,mixed> $content2Descriptor
     */
    private array $content2Descriptor;

    /**
     * @var array<mixed,mixed> $differenceDescriptor
     */
    private array $differenceDescriptor;

    private const array STATUS_KEYS = [
        "for not changed value" => "not changed",
        "for changed value" => "changed",
        "for added value" => "added",
        "for deleted value" => "deleted",
        "for empty value" => "empty",
        "for new value" => "new value"
    ];

    public function __construct(FileReaderInterface $reader)
    {
        $this->filesDataItems = [];

        $this->fileReader = $reader;
    }

    /**
     * @param mixed $data
     */
    private function normalizeData(mixed $data): mixed
    {
        $normalizedValue = $data;
        if (is_bool($data)) {
            $normalizedValue = $data ? "true" : "false";
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

    /**
     * @return array<int,string>
     */
    private function getNextContentKeys(
        mixed $file1Item,
        mixed $file2Item,
        string $fileKey = ""
    ): array {
        if ($fileKey !== "") {
            $file1Content = $this->getNextItemContent($file1Item, $fileKey);
            $file2Content = $this->getNextItemContent($file2Item, $fileKey);
        } else {
            $file1Content = $file1Item;
            $file2Content = $file2Item;
        }

        $contentKeys = array_keys(array_merge(
            is_array($file1Content) ? $file1Content : [],
            is_array($file2Content) ? $file2Content : []
        ));
        asort($contentKeys);

        return $contentKeys;
    }

    private function getNextItemContent(
        mixed $fileItem,
        string $fileKey
    ): mixed {
        if (is_array($fileItem)) {
            return $fileItem[$fileKey] ?? null;
        }

        return null;
    }

    /**
     * @return array<string,mixed>
     * @param array<mixed> $fileContent
     */
    private function getInitContentDescriptor(
        int $level,
        string $fileKey,
        string $history,
        array $fileContent
    ): array {
        return [
            "level" => $level,
            "fileKey" => $fileKey,
            "history" => $history,
            "fileContent" => $fileContent,
            "output" => []
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function getNextInitContentDescriptor(
        int $nextLevel,
        string $nextHistory,
        string $fileKey,
        mixed $fileContent
    ): array {
        return [
            "level" => $nextLevel,
            "history" => $nextHistory,
            "fileKey" => $fileKey,
            "fileContent" => $fileContent,
            "output" => []
        ];
    }

    /**
     * @param array<string> $fileContentKeys
     * @param array<string,mixed> $initContentDescriptor
     * @return array<mixed>
     */
    private function getContent(
        array $fileContentKeys,
        array $initContentDescriptor
    ): array {
        $result = array_reduce(
            $fileContentKeys,
            function ($contentDescriptor, $fileKey) {
                $fileItem = is_array($contentDescriptor) ? $contentDescriptor['fileContent'] : "";
                $currentHistory = is_array($contentDescriptor) ? $contentDescriptor["history"] : "";
                $currentLevel = is_array($contentDescriptor) ? $contentDescriptor["level"] : 0;

                $nextLevel = is_integer($currentLevel) ? $currentLevel + 1 : 0;
                $nextHistory = (($currentHistory !== "") && (is_string($currentHistory))) ?
                    $currentHistory . "." . $fileKey : $fileKey;

                $fileContent = $this->getNextItemContent($fileItem, $fileKey);

                $nextInitContentDescriptor = $this->getNextInitContentDescriptor(
                    $nextLevel,
                    $nextHistory,
                    $fileKey,
                    $fileContent
                );
                $nextContentKeys = $this->getNextContentKeys($fileItem, $fileItem, $fileKey);

                if (is_array($contentDescriptor) && is_array($contentDescriptor['output'])) {
                    $contentDescriptor['output'][$fileKey] = $this->getContent(
                        $nextContentKeys,
                        $nextInitContentDescriptor
                    );
                }

                return $contentDescriptor;
            },
            $initContentDescriptor
        );

        if (is_array($result)) {
            return $result;
        } else {
            return [];
        }
    }

    /**
     * @param mixed $file1Item
     * @param mixed $file2Item
     * @param string $fileKey
     * @param string $currentStatus
     */
    private function getNextItemStatus(
        $file1Item,
        $file2Item,
        $fileKey,
        $currentStatus
    ): string {
        $nextItemIsNotArray = !(is_array($file1Item) && is_array($file2Item));

        $file1Content = $this->getNextItemContent($file1Item, $fileKey);
        $file2Content = $this->getNextItemContent($file2Item, $fileKey);

        $status = self::STATUS_KEYS["for not changed value"];
        $bothFilesKeySet = isset($file1Content, $file2Content);
        $file1KeyOnlySet = isset($file1Content) && !isset($file2Content);
        $file2KeyOnlySet = !isset($file1Content) && isset($file2Content);

        if ($bothFilesKeySet) {
            $status = ($file1Content === $file2Content) ?
                self::STATUS_KEYS["for not changed value"] : self::STATUS_KEYS["for changed value"];
        } elseif ($file1KeyOnlySet) {
            $status = (($currentStatus === self::STATUS_KEYS["for changed value"]) && $nextItemIsNotArray) ?
                self::STATUS_KEYS["for not changed value"] : self::STATUS_KEYS["for deleted value"];
        } elseif ($file2KeyOnlySet) {
            $status = (($currentStatus === self::STATUS_KEYS["for changed value"]) && $nextItemIsNotArray) ?
                self::STATUS_KEYS["for not changed value"] : self::STATUS_KEYS["for added value"];
        }

        return $status;
    }

    /**
     * @return array<string,mixed>
     */
    private function getInitDifferenceDescriptor(
        int $level,
        string $status,
        string $fileKey,
        string $history,
        mixed $file1Content,
        mixed $file2Content
    ): array {
        return [
            "level" => $level,
            "status" => $status,
            "fileKey" => $fileKey,
            "history" => $history,
            "file1Content" => $file1Content,
            "file2Content" => $file2Content,
            "output" => []
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getNextInitDifferenceDescriptor(
        string $status,
        int $level,
        string $fileKey,
        string $history,
        mixed $file1Content,
        mixed $file2Content
    ): array {
        if ($status === self::STATUS_KEYS["for added value"]) {
            $initDifferenceDescriptor = [
                "file1Content" => $file2Content,
                "file2Content" => $file2Content,
            ];
        } elseif ($status === self::STATUS_KEYS["for deleted value"]) {
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
        $initDifferenceDescriptor["output"] = [];

        return $initDifferenceDescriptor;
    }

    /**
     * @return array<string,string>
     */
    public function getStatusKeys(): array
    {
        return self::STATUS_KEYS;
    }

    /**
     * @param array<int,string> $fileContentKeys
     * @param array<mixed> $initDifferenceDescriptor
     * @return array<mixed>
     */
    private function getDifference(
        $fileContentKeys,
        $initDifferenceDescriptor
    ): array {
        $result = array_reduce(
            $fileContentKeys,
            function ($differenceDescriptor, $fileKey) {
                $file1Item = is_array($differenceDescriptor) ? $differenceDescriptor["file1Content"] : [];
                $file2Item = is_array($differenceDescriptor) ? $differenceDescriptor["file2Content"] : [];
                $currentStatus = is_array($differenceDescriptor) ? $differenceDescriptor['status'] : "";
                $currentHistory = is_array($differenceDescriptor) ? $differenceDescriptor["history"] : "";
                $currentLevel = is_array($differenceDescriptor) ? $differenceDescriptor["level"] : 0;

                $nextStatus = $this->getNextItemStatus(
                    $file1Item,
                    $file2Item,
                    $fileKey,
                    is_string($currentStatus) ? $currentStatus : ""
                );
                $nextLevel = is_integer($currentLevel) ? $currentLevel + 1 : 0;
                $nextHistory = (($currentHistory !== "") && (is_string($currentHistory))) ?
                    $currentHistory . "." . $fileKey : $fileKey;

                $file1Content = $this->getNextItemContent($file1Item, $fileKey);
                $file2Content = $this->getNextItemContent($file2Item, $fileKey);

                $nextInitDifferenceDescriptor = $this->getNextInitDifferenceDescriptor(
                    $nextStatus,
                    $nextLevel,
                    $fileKey,
                    $nextHistory,
                    $file1Content,
                    $file2Content,
                );
                $nextContentKeys = $this->getNextContentKeys($file1Item, $file2Item, $fileKey);

                if (is_array($differenceDescriptor) && is_array($differenceDescriptor["output"])) {
                    $differenceDescriptor["output"][$fileKey] = $this->getDifference(
                        $nextContentKeys,
                        $nextInitDifferenceDescriptor
                    );
                }

                return $differenceDescriptor;
            },
            $initDifferenceDescriptor
        );

        if (is_array($result)) {
            return $result;
        } else {
            return [];
        }
    }

    /**
     * @return array<int,mixed>
     */
    private function getFileDataItems(CLPI $command, FPI $fileParser): array
    {
        $fileNames = $command->getFileNames();
        $this->filesPaths = [
            $fileNames['FILE1'],
            $fileNames['FILE2']
        ];

        $filesDataItems = [];
        foreach ($this->filesPaths as $filePath) {
            $fileReaderContainer = $this->fileReader->readFile($filePath);
            $filesDataItems[] = $fileParser->execute($fileReaderContainer, true);
        }

        return $filesDataItems;
    }

    /**
     * @return array<mixed>
     */
    private function getNormalizedFileContent(mixed $dataItem): array
    {
        if (is_array($dataItem)) {
            return array_map(
                fn($item) => $this->normalizeData($item),
                $dataItem
            );
        }

        return [];
    }

    public function execute(CLPI $command, FPI $fileParser): FDCI
    {
        $this->filesDataItems = $this->getFileDataItems($command, $fileParser);

        $file1Content = $this->getNormalizedFileContent($this->filesDataItems[0]);
        $file2Content = $this->getNormalizedFileContent($this->filesDataItems[1]);

        $initContent1Descriptor = $this->getInitContentDescriptor(
            level: 0,
            fileKey: "initKey",
            history: "",
            fileContent: $file1Content
        );
        $fileKeys = $this->getNextContentKeys($file1Content, $file1Content);

        $this->content1Descriptor = $this->getContent(
            $fileKeys,
            $initContent1Descriptor
        );

        $initContent2Descriptor = $this->getInitContentDescriptor(
            level: 0,
            fileKey: "initKey",
            history: "",
            fileContent: $file2Content
        );
        $fileKeys = $this->getNextContentKeys($file2Content, $file2Content);

        $this->content2Descriptor = $this->getContent(
            $fileKeys,
            $initContent2Descriptor
        );

        $initDifferenceDescriptor = $this->getInitDifferenceDescriptor(
            level: 0,
            status: "init",
            fileKey: "initKey",
            history: "",
            file1Content: $file1Content,
            file2Content: $file2Content
        );
        $mergedFileKeys = $this->getNextContentKeys($file1Content, $file2Content);

        $this->differenceDescriptor = $this->getDifference(
            $mergedFileKeys,
            $initDifferenceDescriptor
        );

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

    /**
     * @return array<mixed,mixed>
     */
    public function getContent1Descriptor(): array
    {
        return $this->content1Descriptor;
    }

    /**
     * @return array<mixed,mixed>
     */
    public function getContent2Descriptor(): array
    {
        return $this->content2Descriptor;
    }

    /**
     * @return array<mixed,mixed>
     */
    public function getDifferenceDescriptor(): array
    {
        return $this->differenceDescriptor;
    }
}
