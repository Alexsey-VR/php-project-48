<?php

namespace Differ\Formatters;

use Differ\Interfaces\FormattersInterface as FI;
use Differ\Interfaces\FilesDiffCommandInterface;

class JSONCommand implements FI
{
    public string $filesContentString;
    public string $filesDiffsString;

    public function execute(FilesDiffCommandInterface $command): FI
    {
        $content1Descriptor = $command->getContent1Descriptor();
        $content2Descriptor = $command->getContent2Descriptor();
        $differenceDescriptor = $command->getDifferenceDescriptor();

        $jsonEncoded1Descriptor = json_encode(
            $content1Descriptor,
            flags: JSON_PRETTY_PRINT
        );
        $jsonEncoded2Descriptor = json_encode(
            $content2Descriptor,
            flags: JSON_PRETTY_PRINT
        );

        $this->filesContentString = "File {$command->getFile1Name()} content:\n{$jsonEncoded1Descriptor}\n" .
            "File {$command->getFile2Name()} content:\n{$jsonEncoded2Descriptor}\n";

        $jsonDiffEncoded = json_encode(
            $differenceDescriptor["output"],
            flags: JSON_PRETTY_PRINT
        );
        $this->filesDiffsString = "{$jsonDiffEncoded}\n";

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
