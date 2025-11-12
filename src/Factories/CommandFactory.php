<?php

namespace Differ\Factories;

use Differ\Interfaces\CommandLineParserInterface as CLPI;
use Differ\Interfaces\FileParserInterface as FPI;
use Differ\Interfaces\FilesDiffCommandInterface as FDCI;
use Differ\Interfaces\FormattersInterface as FI;
use Differ\Interfaces\DisplayCommandInterface as DCI;
use Differ\Interfaces\DocoptDoubleInterface;
use Differ\Interfaces\FileReaderInterface;

class CommandFactory implements \Differ\Interfaces\CommandFactoryInterface
{
    private \Docopt|\Differ\Interfaces\DocoptDoubleInterface $parser;
    private FileReaderInterface $fileReader;
    private \Differ\Interfaces\CommandFactoryInterface $formatters;

    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    public function __construct(
        \Docopt|DocoptDoubleInterface $parser,
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

    public function createCommand(string $commandType): CLPI | FDCI | FI | DCI | FPI
    {
        switch ($commandType) {
            case "parseCMDLine":
                $requestedCommand = new \Differ\Parsers\CommandLineParser($this->parser);
                break;
            case "parseFile":
                $requestedCommand = new \Differ\Parsers\FileParser();
                break;
            case "difference":
                $requestedCommand = new \Differ\FilesDiffCommand($this->fileReader);
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
                $requestedCommand = new \Differ\DisplayCommand();
                break;
            default:
                throw new \Differ\DifferException("internal error: unknown command factory option\n");
        }
        return $requestedCommand;
    }
}
