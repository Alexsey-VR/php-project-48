<?php

namespace Differ;

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
        $parseCommand = $this->commandFactory->getCommand("parse");
        $this->nextCommand = $parseCommand
                                  ->execute();

        $differenceCommand = $this->commandFactory->getCommand("difference");
        $this->nextCommand = $differenceCommand
                                  ->execute($this->nextCommand);

        $formatCommand = $this->commandFactory->getCommand("format");
        $this->nextCommand = $formatCommand->selectFormat($parseCommand)
                                        ->execute($this->nextCommand);

        $displayCommand = $this->commandFactory->getCommand("show");
        $displayCommand->execute($this->nextCommand);
    }
}
