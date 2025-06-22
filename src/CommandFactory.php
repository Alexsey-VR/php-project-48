<?php

namespace Differ;

class CommandFactory implements CommandFactoryInterface
{
    private string $docopt;
    private const COMMAND_LIST = ["parse", "difference", "show"];

    public function __construct(string $docopt = '')
    {
        $this->docopt = $docopt;
    }

    public function getCommandTypeList(): array
    {
        return CommandFactory::COMMAND_LIST;
    }

    public function getCommand(string $commandType): CommandInterface | CommandLineParserInterface
    {
        switch ($commandType) {
            case self::COMMAND_LIST[0]:
                return new CommandLineParser($this->docopt);
            case self::COMMAND_LIST[1]:
                return new FilesDiffCommand();
            case self::COMMAND_LIST[2]:
                return new DisplayCommand();
            default:
                throw new \Exception("Unknown command");
        }
    }
}
