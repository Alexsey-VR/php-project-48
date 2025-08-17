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
                $requestedCommand = new CommandLineParser($this->docopt);
                break;
            case "difference":
                $requestedCommand = new FilesDiffCommand();
                break;
            case "show":
                $requestedCommand = new DisplayCommand();
                break;
            default:
                return null;
        }
        return $requestedCommand;
    }
}
