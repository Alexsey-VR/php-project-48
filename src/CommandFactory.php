<?php

namespace Differ;

class CommandFactory implements CommandFactoryInterface
{
    private string $docopt;

    public function __construct(string $docopt = '')
    {
        $this->docopt = $docopt;
    }

    public function getCommand(string $commandType): ?CommandInterface
    {
        switch ($commandType) {
            case "parse":
                return new CommandLineParser($this->docopt);
            case "difference":
                return new FilesDiffCommand();
            case "show":
                return new DisplayCommand();
            default:
                return null;
        }
    }
}
