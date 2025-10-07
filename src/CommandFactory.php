<?php

namespace Differ;

use Differ\CommandFactoryInterface;
use Differ\FileReaderInterface;
use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\Formatters;
use Differ\DisplayCommand;
use Differ\DifferException;

class CommandFactory implements CommandFactoryInterface
{
    private \Docopt $parser;
    private FileReaderInterface $fileReader;
    private CommandFactoryInterface $formatters;

    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    public function __construct(
        \Docopt $parser,
        FileReaderInterface $fileReader,
        CommandFactoryInterface $formatters
    ) {
        $this->parser = $parser;
        $this->fileReader = $fileReader;
        $this->formatters = $formatters;
    }

    /**
     * @return array<string,string>
     */
    public function getFormatKeys(): array
    {
        return self::FORMAT_KEYS;
    }

    /**
     * @return mixed
     */
    public function createCommand(string $commandType): mixed
    {
        switch ($commandType) {
            case "parse":
                $requestedCommand = new CommandLineParser($this->parser);
                break;
            case "difference":
                $requestedCommand = new FilesDiffCommand($this->fileReader);
                break;
            case self::FORMAT_KEYS["stylish"]:
                $requestedCommand = $this->formatters->createCommand(self::FORMAT_KEYS["stylish"]);
                break;
            case self::FORMAT_KEYS["plain"]:
                $requestedCommand = $this->formatters->createCommand(self::FORMAT_KEYS["plain"]);
                break;
            case self::FORMAT_KEYS["json"]:
                $requestedCommand = $this->formatters->createCommand(self::FORMAT_KEYS["json"]);
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
