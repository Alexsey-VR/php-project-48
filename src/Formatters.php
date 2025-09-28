<?php

namespace Differ;

use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;

class Formatters implements CommandInterface
{
    private CommandInterface $formatCommand;
    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    private function createCommand(string $commandKey): CommandInterface
    {
        switch ($commandKey) {
            case self::FORMAT_KEYS["stylish"]:
                return new StylishCommand();
            case self::FORMAT_KEYS["plain"]:
                return new PlainCommand();
            case self::FORMAT_KEYS["json"]:
                return new JSONCommand();
            default:
                return throw new DifferException("input error: unknown output format\nUse gendiff -h\n");
        }
    }

    public function selectFormat(CommandInterface $command = null): CommandInterface
    {
        $commandFactory = new CommandFactory(
            new \Docopt(),
            new FileReader()
        );
        $currentFormat = strtolower($command->getFormat());
        //$formatKeys = $commandFactory->getFormatKeys();

        $this->formatCommand = $this->createCommand(
            $currentFormat
        );

        return $this;
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        $this->formatCommand = $this->formatCommand->execute($command);

        return $this;
    }

    public function getFilesContent(): string
    {
        return $this->formatCommand->filesContentString;
    }

    public function getFilesDiffs(): string
    {
        return $this->formatCommand->filesDiffsString;
    }
}
