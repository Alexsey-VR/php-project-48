<?php

namespace Differ\Formatters;

use Differ\CommandInterface;

class PlaneCommand implements CommandInterface
{
    private string $file1ContentString;
    private string $file2ContentString;
    private array $statusKeys;
    private array $statusPrefixes;
    private const array NORMALIZED_VALUES = [
        'false', 'true', 'null', '[complex value]'
    ];
    public string $filesContentString;
    public string $filesDiffsString;

    private function planeContent(array $content): array
    {
        return array_reduce(
            $content,
            function ($result, $contentItem) {
                if (isset($contentItem["output"])) {
                    $result[] = implode($this->planeContent($contentItem["output"])) .
                                "\n";
                } else {
                    $result[] = "Property '{$contentItem['history']}' has value " .
                                "{$this->normalizeValue($contentItem["fileContent"])}\n";
                }

                return $result;
            },
            []
        );
    }

    private function normalizeValue($value): string
    {
        $firstNormalizedValue = is_array($value) ?
            self::NORMALIZED_VALUES[3] : $value;

        return
            in_array(true, array_filter(
                self::NORMALIZED_VALUES,
                function (string $value) use ($firstNormalizedValue) {
                    return $value === $firstNormalizedValue;
                }
            )) ?
            $firstNormalizedValue : "'" . $firstNormalizedValue . "'";
    }

    private function getPlaneItem(
        $contentItem,
        $prefixKey,
        $firstContent,
        $secondContent,
    ): string {
        $firstContentValue = $this->normalizeValue($firstContent);
        $secondContentValue = $this->normalizeValue($secondContent);

        $altComment = "";
        if ($prefixKey === $this->statusKeys[1]) {
            $altComment = ". From {$firstContentValue} to {$secondContentValue}";
        } elseif ($prefixKey === $this->statusKeys[2]) {
            $altComment = " with value: {$secondContentValue}";
        }

        return ($this->statusPrefixes[$prefixKey] !== $this->statusPrefixes[$this->statusKeys[0]]) ?
            "Property '{$contentItem['history']}' was {$this->statusPrefixes[$prefixKey]}{$altComment}"
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
            return "Property '{$contentItem['history']}' was " .
                "{$this->statusPrefixes[$this->statusKeys[2]]} with value: " .
                self::NORMALIZED_VALUES[3];
        } elseif (
            $this->statusPrefixes[$currentPrefixKey] === $this->statusPrefixes[$this->statusKeys[3]]
        ) {
            return "Property '{$contentItem['history']}' was {$this->statusPrefixes[$this->statusKeys[3]]}";
        }
        return implode($currentItemList);
    }

    private function planeDifference(array $content): array
    {
        return array_reduce(
            $content,
            function ($result, $contentItem) {
                $firstContent = $contentItem["file1Content"];
                $secondContent = $contentItem["file2Content"];
                $bothContentIsArray = is_array($firstContent) && is_array($secondContent);

                if ($bothContentIsArray) {
                    $styledItem = $this->getPlaneList(
                        contentItem: $contentItem,
                        currentItemList: $this->planeDifference($contentItem["output"]),
                        prefixKey: $this->statusKeys[0],
                        altPrefixKey: $contentItem["status"],
                        commentKey: $this->statusKeys[0],
                        altCommentKey: $contentItem["status"],
                    );
                } else {
                    $styledItem = $this->getPlaneItem(
                        contentItem: $contentItem,
                        prefixKey: $contentItem["status"],
                        firstContent: $firstContent,
                        secondContent: $secondContent
                    );
                }
                $result[] = "{$styledItem}\n";

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

            $file1Content = $this->planeContent($content1Descriptor["output"]);
            $file2Content = $this->planeContent($content2Descriptor["output"]);

            $file1ContentList = explode("\n", implode("", $file1Content));
            $file2ContentList = explode("\n", implode("", $file2Content));

            $file1PlaneContent = array_filter(
                $file1ContentList,
                fn($item) => $item !== ""
            );
            $file2PlaneContent = array_filter(
                $file2ContentList,
                fn($item) => $item !== ""
            );

            $this->file1ContentString = "File {$file1Name} content:\n" .
                implode("\n", $file1PlaneContent) . "\n";
            $this->file2ContentString = "File {$file2Name} content:\n" .
                implode("\n", $file2PlaneContent) . "\n";

            $this->filesContentString = $this->file1ContentString . $this->file2ContentString;

            $filesDiffs = $this->planeDifference($differenceDescriptor["output"]);

            $filesDiffsList = explode("\n", implode("", $filesDiffs));

            $filesPlaneDiffs = array_filter(
                $filesDiffsList,
                fn($item) => $item !== ""
            );

            $this->filesDiffsString = implode("\n", $filesPlaneDiffs) . "\n";
        }

        return $this;
    }
}
