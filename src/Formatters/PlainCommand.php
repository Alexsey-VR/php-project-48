<?php

namespace Differ\Formatters;

use Differ\CommandInterface;
use Differ\FilesDiffCommandInterface;
use Differ\FormattersInterface;

class PlainCommand implements FormattersInterface
{
    private string $file1ContentString;
    private string $file2ContentString;

    /**
     * @var array<int,string>
     */
    private array $statusKeys;

    /**
     * @var array<string,string>
     */
    private array $statusPrefixes;

    private const array NORMALIZED_VALUES = [
        'false', 'true', 'null', '[complex value]'
    ];
    public string $filesContentString;
    public string $filesDiffsString;

    /**
     * @return array<mixed>
     * @param array<mixed,mixed> $content
     */
    private function plainContent(array $content): array
    {
        $result = array_reduce(
            $content,
            function ($result, $contentItem) {
                if (is_array($contentItem) && is_array($result)) {
                    $contentOutput = is_array($contentItem["output"]) ? $contentItem["output"] : null;
                    if (isset($contentOutput)) {
                        $result[] = implode($this->plainContent($contentOutput)) .
                                    "\n";
                    } else {
                        $strContentHistory = is_string($contentItem['history']) ? $contentItem['history'] : "";
                        $result[] = "Property '{$strContentHistory}' has value " .
                                    "{$this->normalizeValue($contentItem["fileContent"])}\n";
                    }
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
        $strValue = is_string($value) ? $value : "";
        $firstNormalizedValue = is_array($value) ?
            self::NORMALIZED_VALUES[3] : $strValue;

        return
            in_array(true, array_filter(
                self::NORMALIZED_VALUES,
                function (string $value) use ($firstNormalizedValue) {
                    return strcmp($value, $firstNormalizedValue) === 0 ||
                        is_numeric($firstNormalizedValue); // || is_bool($firstNormalizedValue);
                }
            )) ?
            $firstNormalizedValue : "'" . $firstNormalizedValue . "'";
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
        if ($prefixKey === $this->statusKeys[1]) {
            $altComment = ". From {$firstContentValue} to {$secondContentValue}";
        } elseif ($prefixKey === $this->statusKeys[2]) {
            $altComment = " with value: {$secondContentValue}";
        }

        $strHistory = "";
        if (is_array($contentItem)) {
            $strHistory = is_string($contentItem['history']) ? $contentItem['history'] : "";
        }
        return ($this->statusPrefixes[$prefixKey] !== $this->statusPrefixes[$this->statusKeys[0]]) ?
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
        $currentCommentKey = "";
        if (is_array($contentItem)) {
            $currentPrefixKey = (is_array($contentItem["output"]) &&
                ($contentItem["status"] === $this->statusKeys[1])) ?
                $prefixKey : $altPrefixKey;

            $currentCommentKey = ($contentItem["status"] === $this->statusKeys[1]) ?
                $commentKey : $altCommentKey;

            $strHistory = is_string($contentItem['history']) ? $contentItem['history'] : "";
            if ($currentCommentKey === $this->statusKeys[2]) {
                return "Property '{$strHistory}' was " .
                    "{$this->statusPrefixes[$this->statusKeys[2]]} with value: " .
                    self::NORMALIZED_VALUES[3];
            } elseif (
                $this->statusPrefixes[$currentPrefixKey] === $this->statusPrefixes[$this->statusKeys[3]]
            ) {
                return "Property '{$strHistory}' was {$this->statusPrefixes[$this->statusKeys[3]]}";
            }
        }

        return implode($currentItemList);
    }

    /**
     * @param array<mixed,mixed> $content
     * @return array<mixed,mixed>
     */
    private function plainDifference(array $content): array
    {
        $result = array_reduce(
            $content,
            function ($result, $contentItem) {
                $result = [];
                if (is_array($contentItem)) {
                    $firstContent = $contentItem["file1Content"];
                    $secondContent = $contentItem["file2Content"];
                    $bothContentIsArray = is_array($firstContent) && is_array($secondContent);

                    $contentOutput = [];
                    if (array_key_exists("output", $contentItem)) {
                        $contentOutput = is_array($contentItem["output"]) ?
                            $contentItem["output"] : [];
                    }

                    $strStatus = "";
                    if (array_key_exists("status", $contentItem)) {
                        $strStatus = is_string($contentItem["status"]) ?
                            $contentItem["status"] : "";
                    }

                    if ($bothContentIsArray) {
                        $styledItem = $this->getPlainList(
                            contentItem: $contentItem,
                            currentItemList: $this->plainDifference($contentOutput),
                            prefixKey: $this->statusKeys[0],
                            altPrefixKey: $strStatus,
                            commentKey: $this->statusKeys[0],
                            altCommentKey: $strStatus,
                        );
                    } else {
                        $styledItem = $this->getPlainItem(
                            contentItem: $contentItem,
                            prefixKey: $strStatus,
                            firstContent: $firstContent,
                            secondContent: $secondContent
                        );
                    }
                    $result[] = "{$styledItem}\n";
                }

                return $result;
            },
            []
        );

        return $result;
    }

    /**
     * @return FormattersInterface
     */
    public function execute(FilesDiffCommandInterface $command): FormattersInterface
    {
        $file1Name = $command->getFile1Name();
        $file2Name = $command->getFile2Name();
        $content1Descriptor = $command->getContent1Descriptor();
        $content2Descriptor = $command->getContent2Descriptor();
        $differenceDescriptor = $command->getDifferenceDescriptor();

        $this->statusKeys = $command->getStatusKeys();
        $this->statusPrefixes = [
            $this->statusKeys[0] => "",
            $this->statusKeys[1] => "updated",
            $this->statusKeys[2] => "added",
            $this->statusKeys[3] => "removed"
        ];

        $contentDesc1Output = is_array($content1Descriptor["output"]) ? $content1Descriptor["output"] : [];
        $contentDesc2Output = is_array($content2Descriptor["output"]) ? $content2Descriptor["output"] : [];
        $file1Content = $this->plainContent($contentDesc1Output);
        $file2Content = $this->plainContent($contentDesc2Output);

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

        $this->file1ContentString = "File {$file1Name} content:\n" .
            implode("\n", $file1PlainContent) . "\n";
        $this->file2ContentString = "File {$file2Name} content:\n" .
            implode("\n", $file2PlainContent) . "\n";

        $this->filesContentString = $this->file1ContentString . $this->file2ContentString;

        $diffDescOutput = is_array($differenceDescriptor["output"]) ? $differenceDescriptor["output"] : [];
        $filesDiffs = $this->plainDifference($diffDescOutput);

        $filesDiffsList = explode("\n", implode("", $filesDiffs));

        $filesPlainDiffs = array_filter(
            $filesDiffsList,
            fn($item) => $item !== ""
        );

        $this->filesDiffsString = implode("\n", $filesPlainDiffs) . "\n";

        return $this;
    }

    /**
     * @return string
     */
    public function getContentString(): string
    {
        return $this->filesContentString;
    }

    /**
     * @return string
     */
    public function getDiffsString(): string
    {
        return $this->filesDiffsString;
    }
}
