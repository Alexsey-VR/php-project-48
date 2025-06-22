<?php

namespace Differ;

interface CommandFactoryInterface
{
    public function getCommandTypeList(): array;
    public function getCommand(string $commandType): CommandInterface | CommandLineParserInterface;
}
