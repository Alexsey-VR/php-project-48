<?php

namespace Differ\Factories;

class Formatters implements \Differ\Interfaces\CommandFactoryInterface
{
    private \Differ\Interfaces\FormattersInterface $formatCommand;
    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    public function createCommand(string $commandKey): \Differ\Interfaces\FormattersInterface
    {
        switch ($commandKey) {
            case self::FORMAT_KEYS["stylish"]:
                $this->formatCommand = new \Differ\Formatters\StylishCommand();
                break;
            case self::FORMAT_KEYS["plain"]:
                $this->formatCommand = new \Differ\Formatters\PlainCommand();
                break;
            case self::FORMAT_KEYS["json"]:
                $this->formatCommand = new \Differ\Formatters\JSONCommand();
                break;
            default:
                return throw new \Differ\DifferException("input error: unknown output format\n");
        }

        return $this->formatCommand;
    }
}
