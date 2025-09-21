<?php

namespace Differ;

use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlaneCommand;

class CommandFactory implements CommandFactoryInterface
{
    private $parser;
    private $fileReader;

    public function __construct(
        $parser,
        $fileReader
    ) {
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
            case "stylish":
                $requestedCommand = new StylishCommand();
                break;
            case "plane":
                $requestedCommand = new PlaneCommand();
                break;
            case "format":
                $requestedCommand = new Formatters();
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
