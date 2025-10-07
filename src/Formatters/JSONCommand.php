<?php

namespace Differ\Formatters;

use Differ\CommandInterface;
use Differ\FormattersInterface;

class JSONCommand implements FormattersInterface
{
    private string $files1ContentString;
    private string $files2ContentString;
    public string $filesContentString;
    public string $filesDiffsString;

    public function execute(CommandInterface $command): FormattersInterface
    {
        $file1Name = $command->getFile1Name();
        $file2Name = $command->getFile2Name();
        $content1Descriptor = $command->getContent1Descriptor();
        $content2Descriptor = $command->getContent2Descriptor();
        $differenceDescriptor = $command->getDifferenceDescriptor();

        $this->files1ContentString = "File {$file1Name} content:\n" .
            json_encode(
                $content1Descriptor,
                flags: JSON_PRETTY_PRINT
            ) . "\n";

        $this->files2ContentString = "File {$file2Name} content:\n" .
            json_encode(
                $content2Descriptor,
                flags: JSON_PRETTY_PRINT
            ) . "\n";

        $this->filesContentString = $this->files1ContentString .
                $this->files2ContentString;

        $this->filesDiffsString = json_encode(
            $differenceDescriptor["output"],
            flags: JSON_PRETTY_PRINT
        ) . "\n";

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
