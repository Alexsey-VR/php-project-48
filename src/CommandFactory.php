<?php

namespace Differ;

use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;
use Differ\CommandLineParser;

class CommandFactory implements CommandFactoryInterface
{
    private mixed $parser;
    private FileReaderInterface $fileReader;
    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    public function __construct(
        mixed $parser,
        FileReaderInterface $fileReader
    ) {
        $this->parser = $parser;
        $this->fileReader = $fileReader;
    }

    public function getFormatKeys(): array
    {
        return self::FORMAT_KEYS;
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
            case self::FORMAT_KEYS["stylish"]:
                $requestedCommand = new StylishCommand();
                break;
            case self::FORMAT_KEYS["plain"]:
                $requestedCommand = new PlainCommand();
                break;
            case self::FORMAT_KEYS["json"]:
                $requestedCommand = new JSONCommand();
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
