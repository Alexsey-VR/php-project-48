<?php

namespace Differ\Formatters;

use Differ\CommandInterface;

class PlaneCommand implements CommandInterface
{
    private string $files1ContentString;
    private string $files2ContentString;
    private string $filesContentString;
    private string $filesDiffsString;
    private array $statusKeys;
    private array $statusPrefixes;
    private array $statusComments;
    private const array NORMALIZED_VALUES = [
        'false', 'true', 'null', '[complex value]'
    ];

    private function stylizeContent(array $content): array
    {
        return array_reduce(
            $content,
            function ($result, $contentItem) {
                $itemLevelShift = str_repeat($this->statusPrefixes[$this->statusKeys[0]], $contentItem["level"]);

                if (isset($contentItem["output"])) {
                    $result[] = $itemLevelShift .
                                "{$contentItem['fileKey']}: ";
                    $result[] = "{" .
                                "\n" . implode($this->stylizeContent($contentItem["output"])) .
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

    private function getPlaneItem(
        $contentItem,
        $prefixKey,
        $history,
        $firstContent,
        $secondContent,
    ): string {
        $firstContentValue = is_array($firstContent) ?
            self::NORMALIZED_VALUES[3] : $firstContent;
        $secondContentValue = is_array($secondContent) ?
            self::NORMALIZED_VALUES[3] : $secondContent;

        $firstContentValue = (
            ($firstContentValue === self::NORMALIZED_VALUES[0]) ||
            ($firstContentValue === self::NORMALIZED_VALUES[1]) ||
            ($firstContentValue === self::NORMALIZED_VALUES[2]) ||
            ($firstContentValue === self::NORMALIZED_VALUES[3])
        ) ? $firstContentValue : "'" . $firstContentValue . "'";

        $secondContentValue = (
            ($secondContentValue === self::NORMALIZED_VALUES[0]) ||
            ($secondContentValue === self::NORMALIZED_VALUES[1]) ||
            ($secondContentValue === self::NORMALIZED_VALUES[2]) ||
            ($secondContentValue === self::NORMALIZED_VALUES[3])
        ) ? $secondContentValue : "'" . $secondContentValue . "'";

        $altComment = "";
        if ($prefixKey === $this->statusKeys[1]) {
            $altComment = ". From {$firstContentValue} to {$secondContentValue}";
        } elseif ($prefixKey === $this->statusKeys[2]) {
            $altComment = " with value: {$secondContentValue}";
        }

        return ($this->statusPrefixes[$prefixKey] !== $this->statusPrefixes[$this->statusKeys[0]]) ?
            "Property '{$contentItem['history']}' was " . $this->statusPrefixes[$prefixKey] .
            $altComment
        :
        "";
    }

    private function getPlaneList(
        $contentItem,
        $currentItemList,
        $prefixKey,
        $altPrefixKey,
        $commentKey,
        $altCommentKey
    ): string {
        $currentPrefixKey = (is_array($contentItem["output"]) &&
            ($contentItem["status"] === $this->statusKeys[1])) ?
            $prefixKey : $altPrefixKey;

        $currentCommentKey = ($contentItem["status"] === $this->statusKeys[1]) ?
            $commentKey : $altCommentKey;

        if ($currentCommentKey === $this->statusKeys[2]) {
            return "Property '{$contentItem['history']}' was added with value: [complex value]";
        } elseif (
            ($currentCommentKey === $this->statusKeys[3]) &&
            ($this->statusPrefixes[$currentPrefixKey] === "removed")
        ) {
            return "Property '{$contentItem['history']}' was removed";
        }
        return implode($currentItemList);
    }

    private function stylizeDifference(array $content): array
    {
        return array_reduce(
            $content,
            function ($result, $contentItem) {
                $firstContent = $contentItem["file1Content"];
                $secondContent = $contentItem["file2Content"];
                $firstContentIsArray = is_array($firstContent) && !is_array($secondContent);
                $secondContentIsArray = !is_array($firstContent) && is_array($secondContent);
                $bothContentIsArray = is_array($firstContent) && is_array($secondContent);

                if ($bothContentIsArray) {
                    $styledArray = $this->getPlaneList(
                        contentItem: $contentItem,
                        currentItemList: $this->stylizeDifference($contentItem["output"]),
                        prefixKey: $this->statusKeys[0],
                        altPrefixKey: $contentItem["status"],
                        commentKey: $this->statusKeys[0],
                        altCommentKey: $contentItem["status"],
                    );

                    $result[] = "{$styledArray}\n";
                } else {
                    $styledItem = $this->getPlaneItem(
                        contentItem: $contentItem,
                        prefixKey: $contentItem["status"],
                        history: $contentItem["history"],
                        firstContent: $firstContent,
                        secondContent: $secondContent
                    );
                    $result[] = "{$styledItem}\n";
                }

                return $result;
            },
            []
        );
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        if (!is_null($command)) {
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
            $this->statusComments = [
                $this->statusKeys[0] => "",
                $this->statusKeys[1] => " from alt ",
                $this->statusKeys[2] => " added -> [complex value]",
                $this->statusKeys[3] => " removed -> x ",
                $this->statusKeys[4] => "''",
                $this->statusKeys[5] => " to alt "
            ];

            $files1Content = $this->stylizeContent($content1Descriptor["output"]);
            $this->files1ContentString = "File {$file1Name} content:\n" .
                "{\n" . implode("", $files1Content) . "}\n";

            $files2Content = $this->stylizeContent($content2Descriptor["output"]);
            $this->files2ContentString = "File {$file2Name} content:\n" .
                "{\n" . implode("", $files2Content) . "}\n";

            $this->filesContentString = $this->files1ContentString .
                    $this->files2ContentString;

            $filesDiffs = $this->stylizeDifference($differenceDescriptor["output"]);

            $filesDiffsList = explode("\n", implode("", $filesDiffs));

            $filesPlaneDiffs = array_filter(
                $filesDiffsList,
                fn($item) => $item !== ""
            );

            $this->filesDiffsString = implode("\n", $filesPlaneDiffs) . "\n";
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
