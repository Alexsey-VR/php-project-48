<?php

namespace Differ;

class CommandFactory implements CommandFactoryInterface
{
    private $parser;
    private $fileReader;
    private $displayMode;

    public function __construct($parser, $fileReader)
    {
        $this->parser = $parser;
        $this->fileReader = $fileReader;
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
            case "show":
                $requestedCommand = new DisplayCommand();
                break;
            default:
                return null;
        }
        return $requestedCommand;
    }
}
