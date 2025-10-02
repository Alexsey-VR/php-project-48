<?php

namespace Differ;

use Differ\CommandLineParser;

class ConsoleApp
{
    private CommandInterface $nextCommand;
    private CommandFactoryInterface $commandFactory;

    public function __construct(
        CommandFactoryInterface $commandFactory
    ) {
        $this->commandFactory = $commandFactory;
    }

    public function run(): void
    {
        $parseCommand = $this->commandFactory->createCommand("parse");
        $this->nextCommand = $parseCommand
                                  ->execute();

        $differenceCommand = $this->commandFactory->createCommand("difference");
        $this->nextCommand = $differenceCommand
                                  ->execute($this->nextCommand);

        $formatCommand = $this->commandFactory->createCommand(
            strtolower($parseCommand->getFormat())
        );
        $this->nextCommand = $formatCommand->execute($this->nextCommand);

        $displayCommand = $this->commandFactory->createCommand("show");

        $displayCommand->execute($this->nextCommand);
    }
}
