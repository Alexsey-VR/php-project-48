<?php

namespace Differ\Differs;

use Differ\Interfaces\FilesDiffCommandInterface as FDCI;
use Differ\DifferException;

class FilesDiffCommand implements FDCI
{
    private \Differ\Interfaces\FileReaderInterface $fileReader;

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
        "not changed", "changed", "added", "deleted", "empty", "new value"
    ];

    public function __construct(\Differ\Interfaces\FileReaderInterface $reader)
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

    /**
     * @param array<string> $fileContentKeys
     * @param array<string,mixed> $initContentDescriptor
     * @return array<mixed,mixed>
     */
    private function getContent(
        array $fileContentKeys,
        array $initContentDescriptor
    ): array {
        $result = array_reduce(
            $fileContentKeys,
            function ($contentDescriptor, $fileKey) {
                $fileItem = is_array($contentDescriptor) ? $contentDescriptor['fileContent'] : "";

                $fileContent = is_array($fileItem) ? $fileItem[$fileKey] : $fileItem;

                $levelNum = is_array($contentDescriptor) ? $contentDescriptor["level"] : 0;
                $level = is_numeric($levelNum) ?
                    ($levelNum + 1) : throw new DifferException("internal error: level is not numeric\n");

                $historyValue = is_array($contentDescriptor) ? $contentDescriptor["history"] : "";
                $historyString = is_string($historyValue) ? $historyValue : "";
                $history = ($historyString === "") ?  $fileKey : $historyString . "." . $fileKey;

                $fileContentKeys = array_keys(
                    is_array($fileContent) ? $fileContent : []
                );
                asort($fileContentKeys);

                $initContentDescriptor = [
                    "level" => $level,
                    "history" => $history,
                    "fileKey" => $fileKey,
                    "fileContent" => $fileContent,
                    "output" => []
                ];

                if (is_array($contentDescriptor)) {
                    if (is_array($contentDescriptor['output'])) {
                        $contentDescriptor['output'][$fileKey] = $this->getContent(
                            $fileContentKeys,
                            $initContentDescriptor
                        );
                    }
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
     * @param null|string|array<string|mixed>|mixed $file1Content
     * @param null|string|array<string|mixed>|mixed $file2Content
     * @param string $currentStatus
     * @param boolean $nextItemIsNotArray
     */
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

    /**
     * @param string $status
     * @param int $level
     * @param string $fileKey
     * @param string $history
     * @param null|mixed $file1Content
     * @param null|mixed $file2Content
     * @return array<string|mixed>
     */
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
        $initDifferenceDescriptor["output"] = [];

        return $initDifferenceDescriptor;
    }

    /**
     * @return array<int,string>
     */
    public function getStatusKeys(): array
    {
        return self::STATUS_KEYS;
    }

    /**
     * @param array<int,string> $fileContentKeys
     * @param array<mixed> $initDifferenceDescriptor
     * @return mixed
     */
    private function getDifference(
        $fileContentKeys,
        $initDifferenceDescriptor
    ): mixed {
        return array_reduce(
            $fileContentKeys,
            function ($differenceDescriptor, $fileKey) {
                $file1Item = is_array($differenceDescriptor) ? $differenceDescriptor["file1Content"] : [];
                $file2Item = is_array($differenceDescriptor) ? $differenceDescriptor["file2Content"] : [];

                $file1Content = null;
                if (is_array($file1Item)) {
                    $file1Content = $file1Item[$fileKey] ?? null;
                }

                $file2Content = null;
                if (is_array($file2Item)) {
                    $file2Content = $file2Item[$fileKey] ?? null;
                }

                $nextItemIsNotArray = !(is_array($file1Item) && is_array($file2Item));

                $currentStatus = is_array($differenceDescriptor) ? $differenceDescriptor['status'] : "";
                $statusData = is_string($currentStatus) ? $currentStatus : "";

                $status = $this->getNextItemStatus(
                    $file1Content,
                    $file2Content,
                    $statusData,
                    $nextItemIsNotArray
                );

                $prevLevel = is_array($differenceDescriptor) ? $differenceDescriptor["level"] : 0;
                $level = is_integer($prevLevel) ? $prevLevel + 1 : 0;

                $history = "";
                if (is_array($differenceDescriptor)) {
                    $nextHistory = $differenceDescriptor["history"];
                    $strHistory = is_string($nextHistory) ? $nextHistory : "";
                    $history = ($differenceDescriptor["history"] === "") ?
                        $fileKey : $strHistory . "." . $fileKey;
                }

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
                    $file2Content,
                );

                if (is_array($differenceDescriptor)) {
                    if (is_array($differenceDescriptor["output"])) {
                        $differenceDescriptor["output"][$fileKey] = $this->getDifference(
                            $contentKeys,
                            $initDifferenceDescriptor
                        );
                    }
                }

                return $differenceDescriptor;
            },
            $initDifferenceDescriptor
        );
    }

    public function execute(
        \Differ\Interfaces\CommandLineParserInterface $command,
        \Differ\Interfaces\FileParserInterface $fileParser
    ): FDCI {
        $fileNames = $command->getFileNames();
        $this->filesPaths = [
            $fileNames['FILE1'],
            $fileNames['FILE2']
        ];

        foreach ($this->filesPaths as $filePath) {
            $fileReaderContainer = $this->fileReader->readFile($filePath);
            $this->filesDataItems[] = $fileParser->execute($fileReaderContainer, true);
        }

        $filesDataItems = $this->filesDataItems[0];
        $file1Content = [];
        if (is_array($filesDataItems)) {
            $file1Content = array_map(
                fn($item) => $this->normalizeData($item),
                $filesDataItems
            );
        }

        $filesDataItems = $this->filesDataItems[1];
        $file2Content = [];
        if (is_array($filesDataItems)) {
            $file2Content = array_map(
                fn($item) => $this->normalizeData($item),
                $filesDataItems
            );
        }

        $fileKeys = array_keys($file1Content);
        asort($fileKeys);
        $initContent1Descriptor = [
            "level" => 0,
            "fileKey" => "initKey",
            "history" => "",
            "fileContent" => $file1Content,
            "output" => []
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
            "fileContent" => $file2Content,
            "output" => []
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
            "output" => []
        ];

        $differenceResult = $this->getDifference(
            $mergedFileKeys,
            $initDifferenceDescriptor
        );

        if (is_array($differenceResult)) {
            $this->differenceDescriptor = $differenceResult;
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
