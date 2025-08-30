<?php

namespace Differ;

interface CommandInterface
{
    public function execute(CommandInterface $command = null): CommandInterface;
}
