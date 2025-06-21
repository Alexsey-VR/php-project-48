<?php

namespace Differ;

class CommandFactory implements CommandFactoryInterface
{
    private string $docopt;

    public function __construct(string $docopt = '')
    {
        $this->docopt = $docopt;
    }

    public function getCommand(string $commandType): CommandInterface | OutputInterface | null
    {
        if (!strcmp($commandType, "parse")) {
            return new Output($this->docopt);
        } elseif (!strcmp($commandType, "difference")) {
            return new FilesDiffCommand();
        } elseif (!strcmp($commandType, "show")) {
            return new DisplayCommand();
        }
        return null;
    }
}
