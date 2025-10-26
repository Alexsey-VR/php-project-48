<?php

namespace Differ;

interface CommandFactoryInterface
{
    public function createCommand(string $commandType): CommandInterface | CommandLineParserInterface;
}
