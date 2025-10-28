<?php

namespace Differ;

use Differ\CommandLineParserInterface as CLP;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\CommandInterface as CI;

class DisplayCommand implements CommandInterface
{
    private string $mode;
    private CommandInterface $formatCommand;
    private const string MODE_EXCEPTION_MESSAGE = "internal error: unknown mode for display\n";
    public const AVAILABLE_MODES = [
        "differents" => "differents",
        "content" => "content"
    ];

    public function __construct(string $mode = self::AVAILABLE_MODES["differents"])
    {
        $this->mode = $mode;
    }

    public function setFormatter(CommandInterface $formatter)
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

    public function execute(CLP|FDCI|CI $command): CommandInterface
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

    public function setMode(string $mode): CommandInterface
    {
        if (in_array($mode, array_keys(self::AVAILABLE_MODES))) {
            $this->mode = $mode;

            return $this;
        } else {
            throw new DifferException(self::MODE_EXCEPTION_MESSAGE);
        }
    }
}
