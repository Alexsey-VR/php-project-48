<?php

namespace Differ;

use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;

class Formatters implements CommandFactoryInterface
{
    private CommandInterface $formatCommand;
    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    public function createCommand(string $commandKey): CommandInterface
    {
        switch ($commandKey) {
            case self::FORMAT_KEYS["stylish"]:
                return new StylishCommand();
            case self::FORMAT_KEYS["plain"]:
                return new PlainCommand();
            case self::FORMAT_KEYS["json"]:
                return new JSONCommand();
            default:
                return throw new DifferException("input error: unknown output format\n");
        }
    }
}
