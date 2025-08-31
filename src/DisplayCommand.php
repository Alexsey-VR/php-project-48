<?php

namespace Differ;

class DisplayCommand implements CommandInterface
{
    // Property to store displaying mode for debug
    private string $mode;
    public const AVAILABLE_MODES = [
        "differents",
        "content"
    ];

    public function __construct(string $mode = self::AVAILABLE_MODES[0])
    {
        $this->mode = $mode;
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        if (!is_null($command)) {
            switch ($this->mode) {
                case self::AVAILABLE_MODES[0]:
                    print_r($command->getFilesDiffs());
                    break;
                case self::AVAILABLE_MODES[1]:
                    print_r($command->getFilesContent());
                    break;
                default:
                    throw new DifferException("internal error: unknown mode for display\n");
            }
        }

        return $this;
    }

    public function setMode(string $mode): CommandInterface
    {
        $this->mode = $mode;

        return $this;
    }
}
