<?php

namespace Differ;

interface CommandFactoryInterface
{
    public function getCommand(string $commandType): CommandInterface | OutputInterface | null;
}
