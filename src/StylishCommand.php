<?php

namespace Differ;

class StylishCommand implements CommandInterface
{
    private string $files1ContentString;
    private string $files2ContentString;
    private string $filesContentString;
    private string $filesDiffsString;
    private array $statusKeys;
    private array $statusPrefixes;
    private array $statusComments;

    private function stylishContent(array $content): array
    {
        return array_reduce(
            $content,
            function ($result, $contentItem) {
                $itemLevelShift = str_repeat($this->statusPrefixes[$this->statusKeys[0]], $contentItem["level"]);

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

    private function getStyledItem(
        $contentItem,
        $prefixKey,
        $currentContent,
        $commentKey,
        $altCommentKey
    ): string {
        $currentCommentKey = ($currentContent === "") ?
            $commentKey  : $altCommentKey;

        return $this->statusPrefixes[$prefixKey] .
            "{$contentItem['fileKey']}: " .
            "{$currentContent}" .
            $this->statusComments[$currentCommentKey];
    }

    private function getStyledList(
        $contentItem,
        $currentItemList,
        $prefixKey,
        $altPrefixKey,
        $commentKey,
        $altCommentKey,
        $itemLevelShift
    ): string {
        $currentPrefixKey = (is_array($contentItem["output"]) &&
            ($contentItem["status"] === $this->statusKeys[1])) ?
            $prefixKey : $altPrefixKey;

        $currentCommentKey = ($contentItem["status"] === $this->statusKeys[1]) ?
            $commentKey : $altCommentKey;

        return $this->statusPrefixes[$currentPrefixKey] .
            "{$contentItem['fileKey']}: {" . $this->statusComments[$currentCommentKey] . "\n" .
            implode($currentItemList) .
            $itemLevelShift . $this->statusPrefixes[$this->statusKeys[0]] .
            "}";
    }

    private function stylishDifference(array $content): array
    {
        return array_reduce(
            $content,
            function ($result, $contentItem) {
                $itemLevelShift = str_repeat($this->statusPrefixes[$this->statusKeys[0]], $contentItem["level"] - 1);

                $firstContent = $contentItem["file1Content"];
                $secondContent = $contentItem["file2Content"];
                $firstContentIsArray = is_array($firstContent) && !is_array($secondContent);
                $secondContentIsArray = !is_array($firstContent) && is_array($secondContent);
                $bothContentIsArray = is_array($firstContent) && is_array($secondContent);

                if ($firstContentIsArray) {
                    $styledArray = $this->getStyledList(
                        contentItem: $contentItem,
                        currentItemList: $this->stylishDifference($contentItem["output"]),
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
                        currentContent: $secondContent,
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
                        currentContent: $firstContent,
                        commentKey: $this->statusKeys[4],
                        altCommentKey: $this->statusKeys[1]
                    );
                    $result[] = $itemLevelShift .
                                $styledItem .
                                "\n";

                    $styledArray = $this->getStyledList(
                        contentItem: $contentItem,
                        currentItemList: $this->stylishDifference($contentItem["output"]),
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
                        currentItemList: $this->stylishDifference($contentItem["output"]),
                        prefixKey: $this->statusKeys[0],
                        altPrefixKey: $contentItem["status"],
                        commentKey: $this->statusKeys[0],
                        altCommentKey: $contentItem["status"],
                        itemLevelShift: $itemLevelShift
                    );
                    $result[] = $itemLevelShift .
                                $styledArray .
                                "\n";
                } elseif ($contentItem["status"] === $this->statusKeys[1]) {
                    $styledItem = $this->getStyledItem(
                        contentItem: $contentItem,
                        prefixKey: $this->statusKeys[3],
                        currentContent: $firstContent,
                        commentKey: $this->statusKeys[4],
                        altCommentKey: $this->statusKeys[1]
                    );
                    $result[] = $itemLevelShift .
                                $styledItem .
                                "\n";

                    $styledItem = $this->getStyledItem(
                        contentItem: $contentItem,
                        prefixKey: $this->statusKeys[2],
                        currentContent: $secondContent,
                        commentKey: $this->statusKeys[4],
                        altCommentKey: $this->statusKeys[5]
                    );
                    $result[] = $itemLevelShift .
                                $styledItem .
                                "\n";
                } elseif (isset($firstContent)) {
                    $styledItem = $this->getStyledItem(
                        contentItem: $contentItem,
                        prefixKey: $contentItem["status"],
                        currentContent: $firstContent,
                        commentKey: $contentItem["status"],
                        altCommentKey: $contentItem["status"]
                    );
                    $result[] = $itemLevelShift .
                                $styledItem .
                                "\n";
                } else {
                    $styledItem = $this->getStyledItem(
                        contentItem: $contentItem,
                        prefixKey: $contentItem["status"],
                        currentContent: $secondContent,
                        commentKey: $contentItem["status"],
                        altCommentKey: $contentItem["status"]
                    );
                    $result[] = $itemLevelShift .
                                $styledItem .
                                "\n";
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
                $this->statusKeys[0] => "    ",
                $this->statusKeys[1] => " -+ ",
                $this->statusKeys[2] => "  + ",
                $this->statusKeys[3] => "  - "
            ];
            $this->statusComments = [
                $this->statusKeys[0] => "",
                $this->statusKeys[1] => " # Старое значение",
                $this->statusKeys[2] => " # Добавлена",
                $this->statusKeys[3] => " # Удалена",
                $this->statusKeys[4] => "# значения нет, но пробел после : есть",
                $this->statusKeys[5] => " # Новое значение"
            ];

            $files1Content = $this->stylishContent($content1Descriptor["output"]);
            $this->files1ContentString = "File {$file1Name} content:\n" .
                "{\n" . implode("", $files1Content) . "}\n";

            $files2Content = $this->stylishContent($content2Descriptor["output"]);
            $this->files2ContentString = "File {$file2Name} content:\n" .
                "{\n" . implode("", $files2Content) . "}\n";

            $this->filesContentString = $this->files1ContentString .
                    $this->files2ContentString;

            $filesDiffs = $this->stylishDifference($differenceDescriptor["output"]);
                $this->filesDiffsString = "{\n" . implode("", $filesDiffs) . "}\n";
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
