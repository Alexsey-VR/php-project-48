<?php

namespace Differ;

class CommandFactory implements CommandFactoryInterface
{
    private string $docopt;

    public function __construct(string $docopt = '')
    {
        $this->docopt = $docopt;
    }

    public function getCommand(string $commandType): CommandInterface | CommandLineParserInterface | null
    {
        if (!strcmp($commandType, "parse")) {
            return new CommandLineParser($this->docopt);
        } elseif (!strcmp($commandType, "difference")) {
            return new FilesDiffCommand();
        } elseif (!strcmp($commandType, "show")) {
            return new DisplayCommand();
        }
        return null;
    }
}
