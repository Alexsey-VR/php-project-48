<?php

namespace Differ;

use Differ\Interfaces\DisplayCommandInterface as DCI;

class DisplayCommand implements DCI
{
    private string $mode;
    private \Differ\Interfaces\FormattersInterface $formatCommand;
    private const string MODE_EXCEPTION_MESSAGE = "internal error: unknown mode for display\n";
    public const AVAILABLE_MODES = [
        "differents" => "differents",
        "content" => "content"
    ];

    public function __construct(string $mode = self::AVAILABLE_MODES["differents"])
    {
        $this->mode = $mode;
    }

    public function setFormatter(\Differ\Interfaces\FormattersInterface $formatter): DCI
    {
        $this->formatCommand = $formatter;

        return $this;
    }

    public function getFilesContent(): string
    {
        return $this->formatCommand->getContentString();
    }

    public function getFilesDiffs(): string
    {
        return $this->formatCommand->getDiffsString();
    }

    public function execute(\Differ\Interfaces\FormattersInterface $command): DCI
    {
        $this->formatCommand = $command;
        switch ($this->mode) {
            case self::AVAILABLE_MODES["differents"]:
                print_r($this->getFilesDiffs());
                break;
            case self::AVAILABLE_MODES["content"]:
                print_r($this->getFilesContent());
                break;
            default:
                throw new DifferException(self::MODE_EXCEPTION_MESSAGE);
        }

        return $this;
    }

    public function setMode(string $mode): DCI
    {
        if (in_array($mode, array_keys(self::AVAILABLE_MODES), true)) {
            $this->mode = $mode;

            return $this;
        } else {
            throw new DifferException(self::MODE_EXCEPTION_MESSAGE);
        }
    }
}
