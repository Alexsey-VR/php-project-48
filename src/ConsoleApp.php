<?php

namespace Differ;

class ConsoleApp
{
    private CommandInterface $currentCommand;
    private CommandInterface $nextCommand;
    private CommandFactoryInterface $commandFactory;

    public function __construct(
        CommandFactoryInterface $commandFactory
    ) {
        $this->commandFactory = $commandFactory;
    }

    public function run(): void
    {
        $this->currentCommand = $this->commandFactory->getCommand("parse");
        $this->nextCommand = $this->currentCommand
                                  ->execute();

        $this->currentCommand = $this->commandFactory->getCommand("difference");
        $this->nextCommand = $this->currentCommand
                                  ->execute($this->nextCommand);

        $this->currentCommand = $this->commandFactory->getCommand("show");
        $this->currentCommand
             ->execute($this->nextCommand);
    }
}
