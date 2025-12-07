<?php

namespace Differ\Formatters;

use Differ\Interfaces\FormattersInterface as FI;
use Differ\Interfaces\FilesDiffCommandInterface as FDCI;

class PlainCommand implements FI
{
    private string $file1ContentString;
    private string $file2ContentString;

    /**
     * @var array<string,string> $statusKeys
     */
    private array $statusKeys;

    /**
     * @var array<string,string> $statusPrefixes
     */
    private array $statusPrefixes;

    private const array NORMALIZED_VALUES = [
        'for false value' => 'false',
        'for true value' => 'true',
        'for null value' => 'null',
        'for complex value' => '[complex value]'
    ];
    public string $filesContentString;
    public string $filesDiffsString;

    /**
     * @param array<mixed,mixed> $content
     * @return array<mixed,mixed>
     */
    private function plainContent(array $content): array
    {
        $result = array_reduce(
            $content,
            function ($result, $contentItem) {
                if (
                    is_array($contentItem) &&
                    is_array($contentItem["output"]) &&
                    is_array($result)
                ) {
                    $strHistory = is_string($contentItem['history']) ? $contentItem['history'] : "";
                    $strFileContent = $this->normalizeValue($contentItem["fileContent"]);

                    $contentItemOutput = implode($this->plainContent($contentItem["output"]));
                    $result[] = (sizeof($contentItem["output"]) > 0) ?
                        "{$contentItemOutput}\n"
                        :
                        "Property '{$strHistory}' has value {$strFileContent}\n";
                }

                return $result;
            },
            []
        );
        if (is_array($result)) {
            return $result;
        } else {
            return [];
        }
    }

    private function normalizeValue(mixed $value): string
    {
        $firstNormalizedValue = is_array($value) ? self::NORMALIZED_VALUES['for complex value'] : $value;

        $strNormalValue = $firstNormalizedValue;
        if (is_numeric($firstNormalizedValue)) {
            $strNormalValue = strval($firstNormalizedValue);
        }

        $specialValues = [];
        $specialValues = array_filter(
            self::NORMALIZED_VALUES,
            function (string $item) use ($firstNormalizedValue) {
                $strValue = is_string($firstNormalizedValue) ? $firstNormalizedValue : "";
                return (strcmp($item, $strValue) === 0) ||
                    is_bool($firstNormalizedValue) ||
                    is_numeric($firstNormalizedValue);
            }
        );

        if (is_string($strNormalValue)) {
            $result = sizeof($specialValues) > 0 ?
                $strNormalValue : "'{$strNormalValue}'";
        } else {
            $result = "";
        }

        return $result;
    }

    private function getPlainItem(
        mixed $contentItem,
        string $prefixKey,
        mixed $firstContent,
        mixed $secondContent,
    ): string {
        $firstContentValue = $this->normalizeValue($firstContent);
        $secondContentValue = $this->normalizeValue($secondContent);

        $altComment = "";
        if ($prefixKey === $this->statusKeys["for changed value"]) {
            $altComment = ". From {$firstContentValue} to {$secondContentValue}";
        } elseif ($prefixKey === $this->statusKeys["for added value"]) {
            $altComment = " with value: {$secondContentValue}";
        }

        $historyItem = is_array($contentItem) ? $contentItem['history'] : "";
        $strHistory = is_string($historyItem) ? $historyItem : "";
        return ($this->statusPrefixes[$prefixKey] !== $this->statusPrefixes[
            $this->statusKeys["for not changed value"]
            ]) ?
            "Property '{$strHistory}' was {$this->statusPrefixes[$prefixKey]}{$altComment}"
        :
        "";
    }

    /**
     * @param array<mixed,mixed> $currentItemList
     */
    private function getPlainList(
        mixed $contentItem,
        array $currentItemList,
        string $prefixKey,
        string $altPrefixKey,
        string $commentKey,
        string $altCommentKey
    ): string {
        $currentPrefixKey = "";
        if (is_array($contentItem)) {
            $currentPrefixKey = (is_array($contentItem["output"]) &&
                ($contentItem["status"] === $this->statusKeys["for changed value"])) ?
                $prefixKey : $altPrefixKey;
        }

        $currentCommentKey = "";
        if (is_array($contentItem)) {
            $currentCommentKey = ($contentItem["status"] === $this->statusKeys["for changed value"]) ?
                $commentKey : $altCommentKey;
        }

        $historyItem = is_array($contentItem) ? $contentItem['history'] : "";
        $strHistory = "";
        if (is_array($contentItem)) {
            $strHistory = is_string($historyItem) ? $historyItem : "";
        }

        if ($currentCommentKey === $this->statusKeys["for added value"]) {
            return "Property '{$strHistory}' was " .
                "{$this->statusPrefixes[$this->statusKeys["for added value"]]} with value: " .
                self::NORMALIZED_VALUES['for complex value'];
        } elseif (
            $this->statusPrefixes[$currentPrefixKey] === $this->statusPrefixes[$this->statusKeys["for deleted value"]]
        ) {
            return "Property '{$strHistory}' was {$this->statusPrefixes[$this->statusKeys["for deleted value"]]}";
        }

        return implode($currentItemList);
    }

    /**
     * @param array<mixed,mixed> $content
     * @return array<mixed,mixed>
     */
    private function plainDifference(array $content): array
    {
        $output = array_reduce(
            $content,
            function ($result, $contentItem) {
                $firstContent = [];
                $secondContent = [];
                $statusContent = "";
                if (is_array($contentItem)) {
                    $firstContent = $contentItem["file1Content"];
                    $secondContent = $contentItem["file2Content"];
                    $statusContent = is_string($contentItem["status"]) ? $contentItem["status"] : "";
                }
                $bothContentIsArray = is_array($firstContent) && is_array($secondContent);


                $outputItem = is_array($contentItem) ? $contentItem["output"] : [];
                $outputData = is_array($outputItem) ? $outputItem : [];
                if ($bothContentIsArray) {
                    $styledItem = $this->getPlainList(
                        contentItem: $contentItem,
                        currentItemList: $this->plainDifference($outputData),
                        prefixKey: $this->statusKeys["for not changed value"],
                        altPrefixKey: $statusContent,
                        commentKey: $this->statusKeys["for not changed value"],
                        altCommentKey: $statusContent,
                    );
                } else {
                    $styledItem = $this->getPlainItem(
                        contentItem: $contentItem,
                        prefixKey: $statusContent,
                        firstContent: $firstContent,
                        secondContent: $secondContent
                    );
                }
                if (is_array($result)) {
                    $result[] = "{$styledItem}\n";
                } else {
                    $result = "";
                }

                return $result;
            },
            []
        );

        if (is_array($output)) {
            return $output;
        } else {
            return [];
        }
    }

    public function execute(FDCI $command): FI
    {
        $content1Descriptor = $command->getContent1Descriptor();
        $content2Descriptor = $command->getContent2Descriptor();
        $differenceDescriptor = $command->getDifferenceDescriptor();

        $this->statusKeys = $command->getStatusKeys();
        $this->statusPrefixes = [
            $this->statusKeys["for not changed value"] => "",
            $this->statusKeys["for changed value"] => "updated",
            $this->statusKeys["for added value"] => "added",
            $this->statusKeys["for deleted value"] => "removed"
        ];

        $output1Content = is_array($content1Descriptor["output"]) ? $content1Descriptor["output"] : [];
        $output2Content = is_array($content2Descriptor["output"]) ? $content2Descriptor["output"] : [];
        $file1Content = $this->plainContent($output1Content);
        $file2Content = $this->plainContent($output2Content);

        $file1ContentList = explode("\n", implode("", $file1Content));
        $file2ContentList = explode("\n", implode("", $file2Content));

            $file1PlainContent = array_filter(
                $file1ContentList,
                fn($item) => $item !== ""
            );
            $file2PlainContent = array_filter(
                $file2ContentList,
                fn($item) => $item !== ""
            );

            $file1ContentString = implode("\n", $file1PlainContent);
            $file2ContentString = implode("\n", $file2PlainContent);
            $this->file1ContentString = "File {$command->getFile1Name()} content:\n{$file1ContentString}\n";
            $this->file2ContentString = "File {$command->getFile2Name()} content:\n{$file2ContentString}\n";

            $this->filesContentString = "{$this->file1ContentString}{$this->file2ContentString}";

            $outputDifference = is_array($differenceDescriptor["output"]) ? $differenceDescriptor["output"] : [];
            $filesDiffs = $this->plainDifference($outputDifference);

            $filesDiffsList = explode("\n", implode("", $filesDiffs));

            $filesPlainDiffs = array_filter(
                $filesDiffsList,
                fn($item) => $item !== ""
            );

            $filesPlainDiffsString = implode("\n", $filesPlainDiffs);
            $this->filesDiffsString = "{$filesPlainDiffsString}\n";

        return $this;
    }

    public function getContentString(): string
    {
        return $this->filesContentString;
    }

    public function getDiffsString(): string
    {
        return $this->filesDiffsString;
    }
}
