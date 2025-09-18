<?php

namespace Differ;

class CommandFactory implements CommandFactoryInterface
{
    private $parser;
    private $fileReader;
    private $stylishCommand;

    public function __construct(
        $parser,
        $fileReader,
        $stylishCommand
    ) {
        $this->parser = $parser;
        $this->fileReader = $fileReader;
        $this->stylishCommand = $stylishCommand;
    }

    public function getCommand(string $commandType): ?CommandInterface
    {
        switch ($commandType) {
            case "parse":
                $requestedCommand = new CommandLineParser($this->parser);
                break;
            case "difference":
                $requestedCommand = (new FilesDiffCommand())
                                        ->setFileReader($this->fileReader);
                break;
            case "stylish":
                $requestedCommand = $this->stylishCommand;
                break;
            case "show":
                $requestedCommand = new DisplayCommand();
                break;
            default:
                throw new DifferException("internal error: unknown command factory option\n");
        }
        return $requestedCommand;
    }
}
