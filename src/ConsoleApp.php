<?php

namespace Differ;

use Differ\CommandLineParser;

class ConsoleApp
{
    private mixed $nextCommand;
    private CommandFactoryInterface $commandFactory;
    private array $flowSteps;
    private mixed $initCommand;

    public function __construct(
        CommandFactoryInterface $commandFactory
    ) {
        $this->commandFactory = $commandFactory;

        $parseCommand = $this->commandFactory->createCommand("parse");
        $this->initCommand = $parseCommand->execute($parseCommand);
        $this->flowSteps = [
            "difference",
            strtolower($parseCommand->getFormat()),
            "show"
        ];
    }

    public function run(): void
    {
        $commandFactory = $this->commandFactory;
        foreach ($this->flowSteps as $step) {
            $currentCommand = $commandFactory->createCommand($step);
            $this->nextCommand = $currentCommand->execute($this->initCommand);
            $this->initCommand = $this->nextCommand;
        }
    }
}
