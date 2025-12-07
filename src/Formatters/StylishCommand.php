<?php

namespace Differ\Formatters;

use Differ\Interfaces\FormattersInterface as FI;
use Differ\Interfaces\FilesDiffCommandInterface as FDCI;

class StylishCommand implements FI
{
    private string $files1ContentString;
    private string $files2ContentString;

    /**
     * @var array<string,string> $statusKeys
     */
    private array $statusKeys;

    /**
     * @var array<string,string> $statusPrefixes
     */
    private array $statusPrefixes;

    /**
     * @var array<string,string> $statusComments
     */
    private array $statusComments;

    private string $commentType;
    public string $filesContentString;
    public string $filesDiffsString;
    private const AVAILABLE_COMMENT_TYPES = [
        "short" => "short",
        "verbose" => "verbose"
    ];

    public function __construct(string $commentType = self::AVAILABLE_COMMENT_TYPES["short"])
    {
        $this->commentType = $commentType;
    }

    /**
     * @param array<mixed,mixed> $content
     * @return array<mixed,mixed>
     */
    private function stylizeContent(array $content): array
    {
        $output = array_reduce(
            $content,
            function ($result, $contentItem) {
                if (is_array($contentItem) && is_array($result)) {
                    $levelNumValue = is_integer($contentItem["level"]) ? $contentItem["level"] : 0;
                    $itemLevelShift = str_repeat($this->statusPrefixes[
                        $this->statusKeys["for not changed value"]
                    ], $levelNumValue);

                    $outputValue = is_array($contentItem["output"]) ? $contentItem["output"] : [];
                    $fileKeyItem = is_string($contentItem['fileKey']) ? $contentItem['fileKey'] : "";

                    if (is_string($contentItem["fileContent"])) {
                        $fileContentItem = $contentItem["fileContent"];
                    } elseif (is_numeric($contentItem["fileContent"])) {
                        $fileContentItem = strval($contentItem["fileContent"]);
                    } else {
                        $fileContentItem = "";
                    }

                    if (sizeof($outputValue) > 0) {
                        $contentOutputString = implode($this->stylizeContent($outputValue));
                        $result[] = "{$itemLevelShift}{$fileKeyItem}: {\n{$contentOutputString}{$itemLevelShift}}\n";
                    } else {
                        $normalizedFileContent = $this->normalizeContent($contentItem["fileContent"]);
                        $result[] = "{$itemLevelShift}{$fileKeyItem}: {$normalizedFileContent}\n";
                    }
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

    private function getStyledItem(
        mixed $contentItem,
        string $prefixKey,
        string $currentContent,
        string $commentKey,
        string $altCommentKey
    ): string {
        $currentCommentKey = ($currentContent === "") ?
            $commentKey  : $altCommentKey;

        if (is_array($contentItem)) {
            $strFileKeyItem = is_string($contentItem['fileKey']) ? $contentItem['fileKey'] : "";
            $output = "{$this->statusPrefixes[$prefixKey]}{$strFileKeyItem}: " .
                "{$currentContent}{$this->statusComments[$currentCommentKey]}";
        } else {
            $output = "";
        }

        return $output;
    }

    /**
     * @param array<mixed,mixed> $currentItemList
     */
    private function getStyledList(
        mixed $contentItem,
        array $currentItemList,
        string $prefixKey,
        string $altPrefixKey,
        string $commentKey,
        string $altCommentKey,
        string $itemLevelShift
    ): string {
        if (is_array($contentItem)) {
            $currentPrefixKey = (is_array($contentItem["output"]) &&
                ($contentItem["status"] === $this->statusKeys["for changed value"])) ?
                $prefixKey : $altPrefixKey;

            $currentCommentKey = ($contentItem["status"] === $this->statusKeys["for changed value"]) ?
                $commentKey : $altCommentKey;

            $strFileKeyItem = is_string($contentItem['fileKey']) ? $contentItem['fileKey'] : "";

            $currentItemListString = implode($currentItemList);
            $output = $this->statusPrefixes[$currentPrefixKey] .
                "{$strFileKeyItem}: {{$this->statusComments[$currentCommentKey]}\n{$currentItemListString}" .
                "{$itemLevelShift}{$this->statusPrefixes[$this->statusKeys["for not changed value"]]}}";
        } else {
            $output = "";
        }

        return $output;
    }

    private function normalizeContent(mixed $content): string
    {
        $output = "";
        if (is_string($content)) {
            $output = $content;
        } elseif (is_numeric($content)) {
            $output = strval($content);
        } elseif (is_bool($content)) {
            $output = ($content === true) ? "true" : "false";
        }

        return $output;
    }

    /**
     * @param array<mixed,mixed> $content
     * @return array<mixed,mixed>
     */
    private function stylizeDifference(array $content): array
    {
        $output = array_reduce(
            $content,
            function ($result, $contentItem) {
                if (is_array($contentItem) && is_array($result)) {
                    $numLevelValue = is_integer($contentItem["level"]) ? $contentItem["level"] : 1;
                    $itemLevelShift = str_repeat($this->statusPrefixes[
                        $this->statusKeys["for not changed value"]
                    ], $numLevelValue - 1);

                    $firstContent = $contentItem["file1Content"];
                    $secondContent = $contentItem["file2Content"];
                    $firstContentIsArray = is_array($firstContent) && !is_array($secondContent);
                    $secondContentIsArray = !is_array($firstContent) && is_array($secondContent);
                    $bothContentIsArray = is_array($firstContent) && is_array($secondContent);

                    $outputItem = is_array($contentItem["output"]) ? $contentItem["output"] : [];
                    $strContentStatus = is_string($contentItem["status"]) ? $contentItem["status"] : "";
                    if ($firstContentIsArray) {
                        $styledArray = $this->getStyledList(
                            contentItem: $contentItem,
                            currentItemList: $this->stylizeDifference($outputItem),
                            prefixKey: $this->statusKeys["for deleted value"],
                            altPrefixKey: $this->statusKeys["for deleted value"],
                            commentKey: $this->statusKeys["for changed value"],
                            altCommentKey: $this->statusKeys["for changed value"],
                            itemLevelShift: $itemLevelShift
                        );
                        $result[] = "{$itemLevelShift}{$styledArray}\n";

                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $this->statusKeys["for added value"],
                            currentContent: $this->normalizeContent($secondContent),
                            commentKey: $this->statusKeys["for empty value"],
                            altCommentKey: $this->statusKeys["for new value"]
                        );
                        $result[] = "{$itemLevelShift}{$styledItem}\n";
                    } elseif ($secondContentIsArray) {
                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $this->statusKeys["for deleted value"],
                            currentContent: $this->normalizeContent($firstContent),
                            commentKey: $this->statusKeys["for empty value"],
                            altCommentKey: $this->statusKeys["for changed value"]
                        );
                        $result[] = "{$itemLevelShift}{$styledItem}\n";

                        $styledArray = $this->getStyledList(
                            contentItem: $contentItem,
                            currentItemList: $this->stylizeDifference($outputItem),
                            prefixKey: $this->statusKeys["for added value"],
                            altPrefixKey: $this->statusKeys["for added value"],
                            commentKey: $this->statusKeys["for new value"],
                            altCommentKey: $this->statusKeys["for new value"],
                            itemLevelShift: $itemLevelShift
                        );
                        $result[] = "{$itemLevelShift}{$styledArray}\n";
                    } elseif ($bothContentIsArray) {
                        $styledArray = $this->getStyledList(
                            contentItem: $contentItem,
                            currentItemList: $this->stylizeDifference($outputItem),
                            prefixKey: $this->statusKeys["for not changed value"],
                            altPrefixKey: $strContentStatus,
                            commentKey: $this->statusKeys["for not changed value"],
                            altCommentKey: $strContentStatus,
                            itemLevelShift: $itemLevelShift
                        );
                        $result[] = "{$itemLevelShift}{$styledArray}\n";
                    } elseif ($contentItem["status"] === $this->statusKeys["for changed value"]) {
                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $this->statusKeys["for deleted value"],
                            currentContent: $this->normalizeContent($firstContent),
                            commentKey: $this->statusKeys["for empty value"],
                            altCommentKey: $this->statusKeys["for changed value"]
                        );
                        $result[] = "{$itemLevelShift}{$styledItem}\n";

                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $this->statusKeys["for added value"],
                            currentContent: $this->normalizeContent($secondContent),
                            commentKey: $this->statusKeys["for empty value"],
                            altCommentKey: $this->statusKeys["for new value"]
                        );
                        $result[] = "{$itemLevelShift}{$styledItem}\n";
                    } elseif (isset($firstContent)) {
                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $strContentStatus,
                            currentContent: $this->normalizeContent($firstContent),
                            commentKey: $strContentStatus,
                            altCommentKey: $strContentStatus
                        );
                        $result[] = "{$itemLevelShift}{$styledItem}\n";
                    } else {
                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $strContentStatus,
                            currentContent: $this->normalizeContent($secondContent),
                            commentKey: $strContentStatus,
                            altCommentKey: $strContentStatus
                        );
                        $result[] = "{$itemLevelShift}{$styledItem}\n";
                    }
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
        $file1Name = $command->getFile1Name();
        $file2Name = $command->getFile2Name();
        $content1Descriptor = $command->getContent1Descriptor();
        $content2Descriptor = $command->getContent2Descriptor();
        $differenceDescriptor = $command->getDifferenceDescriptor();

        $this->statusKeys = $command->getStatusKeys();
        $this->statusPrefixes = [
            $this->statusKeys["for not changed value"] => "    ",
            $this->statusKeys["for changed value"] => " -+ ",
            $this->statusKeys["for added value"] => "  + ",
            $this->statusKeys["for deleted value"] => "  - "
        ];

        $altStatusComments = [];
        foreach ($this->statusKeys as $key) {
            $altStatusComments[$key] = "";
        }

        $this->statusComments = (strcmp($this->commentType, self::AVAILABLE_COMMENT_TYPES["verbose"]) === 0) ?
        [
            $this->statusKeys["for not changed value"] => "",
            $this->statusKeys["for changed value"] => " # Old value",
            $this->statusKeys["for added value"] => " # Added",
            $this->statusKeys["for deleted value"] => " # Removed",
            $this->statusKeys["for empty value"] => "# There are no values, but a space exists after the colon",
            $this->statusKeys["for new value"] => " # New value"
        ]
        :
        $altStatusComments;

        if (is_array($content1Descriptor["output"])) {
            $files1ContentString = implode("", $this->stylizeContent($content1Descriptor["output"]));
            $this->files1ContentString = "File {$file1Name} content:\n{\n{$files1ContentString}}\n";
        }

        if (is_array($content2Descriptor["output"])) {
            $files2ContentString = implode("", $this->stylizeContent($content2Descriptor["output"]));
            $this->files2ContentString = "File {$file2Name} content:\n{\n{$files2ContentString}}\n";
        }

        $this->filesContentString = "{$this->files1ContentString}{$this->files2ContentString}";

        if (is_array($differenceDescriptor["output"])) {
            $filesDiffsString = implode("", $this->stylizeDifference($differenceDescriptor["output"]));
            $this->filesDiffsString = "{\n{$filesDiffsString}}\n";
        }

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
