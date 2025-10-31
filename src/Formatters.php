<?php

namespace Differ;

use Differ\Formatters\StylishCommand;
use Differ\Formatters\PlainCommand;
use Differ\Formatters\JSONCommand;

class Formatters implements CommandFactoryInterface
{
    private FormattersInterface $formatCommand;
    private const array FORMAT_KEYS = [
        "stylish" => "stylish",
        "plain" => "plain",
        "json" => "json"
    ];

    public function createCommand(string $commandKey): FormattersInterface
    {
        switch ($commandKey) {
            case self::FORMAT_KEYS["stylish"]:
                $this->formatCommand = new StylishCommand();
                break;
            case self::FORMAT_KEYS["plain"]:
                $this->formatCommand = new PlainCommand();
                break;
            case self::FORMAT_KEYS["json"]:
                $this->formatCommand = new JSONCommand();
                break;
            default:
                return throw new DifferException("input error: unknown output format\n");
        }

        return $this->formatCommand;
    }
}
