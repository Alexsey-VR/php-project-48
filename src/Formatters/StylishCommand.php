<?php

namespace Differ\Formatters;

use Differ\CommandLineParserInterface as CLPI;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\FormattersInterface as FI;

class StylishCommand implements FI
{
    private string $files1ContentString;
    private string $files2ContentString;

    /**
     * @var array<int,string> $statusKeys
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
                    $itemLevelShift = str_repeat($this->statusPrefixes[$this->statusKeys[0]], $levelNumValue);

                    $outputValue = is_array($contentItem["output"]) ? $contentItem["output"] : [];
                    $fileKeyItem = is_string($contentItem['fileKey']) ? $contentItem['fileKey'] : "";
                    //$fileContentItem = is_string($contentItem["fileContent"]) ? $contentItem["fileContent"] : "";
                    if (is_string($contentItem["fileContent"])) {
                        $fileContentItem = $contentItem["fileContent"];
                    } elseif (is_numeric($contentItem["fileContent"])) {
                        $fileContentItem = strval($contentItem["fileContent"]);
                    } else {
                        $fileContentItem = "";
                    }

                    if (sizeof($outputValue) > 0) {
                        $result[] = $itemLevelShift .
                                    "{$fileKeyItem}: ";
                        $result[] = "{" .
                                    "\n" . implode($this->stylizeContent($outputValue)) .
                                    $itemLevelShift .
                                    "}\n";
                    } else {
                        $result[] = $itemLevelShift .
                                    "{$fileKeyItem}: ";
                        $result[] = $contentItem["fileContent"];
                        $result[] = "\n";
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
            $output = $this->statusPrefixes[$prefixKey] .
                "{$strFileKeyItem}: " .
                "{$currentContent}" .
                $this->statusComments[$currentCommentKey];
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
                ($contentItem["status"] === $this->statusKeys[1])) ?
                $prefixKey : $altPrefixKey;

            $currentCommentKey = ($contentItem["status"] === $this->statusKeys[1]) ?
                $commentKey : $altCommentKey;

            $strFileKeyItem = is_string($contentItem['fileKey']) ? $contentItem['fileKey'] : "";

            $output = $this->statusPrefixes[$currentPrefixKey] .
                "{$strFileKeyItem}: {" . $this->statusComments[$currentCommentKey] . "\n" .
                implode($currentItemList) .
                $itemLevelShift . $this->statusPrefixes[$this->statusKeys[0]] .
                "}";
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
                    $itemLevelShift = str_repeat($this->statusPrefixes[$this->statusKeys[0]], $numLevelValue - 1);

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
                            prefixKey: $this->statusKeys[3],
                            altPrefixKey: $this->statusKeys[3],
                            commentKey: $this->statusKeys[1],
                            altCommentKey: $this->statusKeys[1],
                            itemLevelShift: $itemLevelShift
                        );
                        $result[] = $itemLevelShift .
                                    $styledArray .
                                    "\n";

                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $this->statusKeys[2],
                            currentContent: $this->normalizeContent($secondContent),
                            commentKey: $this->statusKeys[4],
                            altCommentKey: $this->statusKeys[5]
                        );
                        $result[] = $itemLevelShift .
                                    $styledItem .
                                    "\n";
                    } elseif ($secondContentIsArray) {
                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $this->statusKeys[3],
                            currentContent: $this->normalizeContent($firstContent),
                            commentKey: $this->statusKeys[4],
                            altCommentKey: $this->statusKeys[1]
                        );
                        $result[] = $itemLevelShift .
                                    $styledItem .
                                    "\n";

                        $styledArray = $this->getStyledList(
                            contentItem: $contentItem,
                            currentItemList: $this->stylizeDifference($outputItem),
                            prefixKey: $this->statusKeys[2],
                            altPrefixKey: $this->statusKeys[2],
                            commentKey: $this->statusKeys[5],
                            altCommentKey: $this->statusKeys[5],
                            itemLevelShift: $itemLevelShift
                        );
                        $result[] = $itemLevelShift .
                                    $styledArray .
                                    "\n";
                    } elseif ($bothContentIsArray) {
                        $styledArray = $this->getStyledList(
                            contentItem: $contentItem,
                            currentItemList: $this->stylizeDifference($outputItem),
                            prefixKey: $this->statusKeys[0],
                            altPrefixKey: $strContentStatus,
                            commentKey: $this->statusKeys[0],
                            altCommentKey: $strContentStatus,
                            itemLevelShift: $itemLevelShift
                        );
                        $result[] = $itemLevelShift .
                                    $styledArray .
                                    "\n";
                    } elseif ($contentItem["status"] === $this->statusKeys[1]) {
                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $this->statusKeys[3],
                            currentContent: $this->normalizeContent($firstContent),
                            commentKey: $this->statusKeys[4],
                            altCommentKey: $this->statusKeys[1]
                        );
                        $result[] = $itemLevelShift .
                                    $styledItem .
                                    "\n";

                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $this->statusKeys[2],
                            currentContent: $this->normalizeContent($secondContent),
                            commentKey: $this->statusKeys[4],
                            altCommentKey: $this->statusKeys[5]
                        );
                        $result[] = $itemLevelShift .
                                    $styledItem .
                                    "\n";
                    } elseif (isset($firstContent)) {
                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $strContentStatus,
                            currentContent: $this->normalizeContent($firstContent),
                            commentKey: $strContentStatus,
                            altCommentKey: $strContentStatus
                        );
                        $result[] = $itemLevelShift .
                                    $styledItem .
                                    "\n";
                    } else {
                        $styledItem = $this->getStyledItem(
                            contentItem: $contentItem,
                            prefixKey: $strContentStatus,
                            currentContent: $this->normalizeContent($secondContent),
                            commentKey: $strContentStatus,
                            altCommentKey: $strContentStatus
                        );
                        $result[] = $itemLevelShift .
                                    $styledItem .
                                    "\n";
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
            $this->statusKeys[0] => "    ",
            $this->statusKeys[1] => " -+ ",
            $this->statusKeys[2] => "  + ",
            $this->statusKeys[3] => "  - "
        ];
        $altStatusComments = [];
        foreach ($this->statusKeys as $key) {
            $altStatusComments[$key] = "";
        }
        $this->statusComments = !strcmp($this->commentType, self::AVAILABLE_COMMENT_TYPES["verbose"]) ?
        [
            $this->statusKeys[0] => "",
            $this->statusKeys[1] => " # Old value",
            $this->statusKeys[2] => " # Added",
            $this->statusKeys[3] => " # Removed",
            $this->statusKeys[4] => "# There are no values, but a space exists after the colon",
            $this->statusKeys[5] => " # New value"
        ]
        :
        $altStatusComments;

        if (is_array($content1Descriptor["output"])) {
            $files1Content = $this->stylizeContent($content1Descriptor["output"]);
            $this->files1ContentString = "File {$file1Name} content:\n" .
                "{\n" . implode("", $files1Content) . "}\n";
        } else {
            $file1Content = [];
        }

        if (is_array($content2Descriptor["output"])) {
            $files2Content = $this->stylizeContent($content2Descriptor["output"]);
            $this->files2ContentString = "File {$file2Name} content:\n" .
                "{\n" . implode("", $files2Content) . "}\n";
        } else {
            $files2Content = [];
        }

        $this->filesContentString = $this->files1ContentString .
                $this->files2ContentString;

        if (is_array($differenceDescriptor["output"])) {
            $filesDiffs = $this->stylizeDifference($differenceDescriptor["output"]);
                $this->filesDiffsString = "{\n" . implode("", $filesDiffs) . "}\n";
        } else {
            $filesDiffs = [];
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
