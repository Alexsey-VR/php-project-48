<?php

namespace Differ;

interface CommandInterface
{
    public function execute(CommandInterface|CommandLineParserInterface $command): CommandInterface;
}
