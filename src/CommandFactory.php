<?php

namespace Differ;

use Differ\Interfaces\CommandLineParserInterface as CLPI;
use Differ\Interfaces\FilesDiffCommandInterface as FDCI;
use Differ\Interfaces\FormattersInterface as FI;
use Differ\Interfaces\DisplayCommandInterface as DCI;

class CommandFactory implements \Differ\Interfaces\CommandFactoryInterface
{
    private \Docopt|\Differ\Interfaces\DocoptDoubleInterface $parser;
    private \Differ\Interfaces\FileReaderInterface $fileReader;
    private \Differ\Interfaces\CommandFactoryInterface $formatters;

    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    public function __construct(
        \Docopt|\Differ\Interfaces\DocoptDoubleInterface $parser,
        \Differ\Interfaces\FileReaderInterface $fileReader,
        \Differ\Interfaces\CommandFactoryInterface $formatters
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

    public function createCommand(string $commandType): CLPI | FDCI | FI | DCI
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
