<?php

namespace Differ;

class DisplayCommand implements DisplayCommandInterface
{
    private string $mode;
    private FormattersInterface $formatCommand;
    private const string MODE_EXCEPTION_MESSAGE = "internal error: unknown mode for display\n";
    public const AVAILABLE_MODES = [
        "differents" => "differents",
        "content" => "content"
    ];

    public function __construct(string $mode = self::AVAILABLE_MODES["differents"])
    {
        $this->mode = $mode;
    }

    /**
     * @return FormattersInterface
     */
    public function setFormatter(FormattersInterface $formatter)
    {
        $this->formatCommand = $formatter;

        return $this->formatCommand;
    }

    /**
     * @return string
     */
    public function getFilesContent(): string
    {
        return $this->formatCommand->getContentString();
    }

    /**
     * @return string
     */
    public function getFilesDiffs(): string
    {
        return $this->formatCommand->getDiffsString();
    }

    /**
     * @return FormattersInterface
     */
    public function execute(FormattersInterface $command): FormattersInterface
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

        return $this->formatCommand;
    }

    /**
     * @return DisplayCommandInterface
     */
    public function setMode(string $mode): DisplayCommandInterface
    {
        if (in_array($mode, array_keys(self::AVAILABLE_MODES))) {
            $this->mode = $mode;

            return $this;
        } else {
            throw new DifferException(self::MODE_EXCEPTION_MESSAGE);
        }
    }
}
