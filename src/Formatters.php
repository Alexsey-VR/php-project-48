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
        $requestedCommand = null;
        switch ($commandKey) {
            case self::FORMAT_KEYS["stylish"]:
                $requestedCommand = new StylishCommand();
                break;
            case self::FORMAT_KEYS["plain"]:
                $requestedCommand = new PlainCommand();
                break;
            case self::FORMAT_KEYS["json"]:
                $requestedCommand = new JSONCommand();
                break;
            default:
                return throw new DifferException("input error: unknown output format\n");
        }

        return $requestedCommand;
    }
}
