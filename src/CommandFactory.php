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
use Differ\CommandLineParserInterface as CLP;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\FormattersInterface as FI;
use Differ\DisplayCommandInterface as DCI;

class CommandFactory implements CommandFactoryInterface
{
    private \Docopt|DocoptDoubleInterface $parser;
    private FileReaderInterface $fileReader;
    private CommandFactoryInterface $formatters;

    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    public function __construct(
        \Docopt|DocoptDoubleInterface $parser,
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

    public function createCommand(string $commandType): CLP|FDCI|FI|DCI
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
