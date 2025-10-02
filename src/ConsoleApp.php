<?php

namespace Differ;

use Differ\CommandLineParser;

class ConsoleApp
{
    private CommandInterface $nextCommand;
    private CommandFactoryInterface $commandFactory;
    private array $flowSteps;
    private CommandInterface $initCommand;

    public function __construct(
        CommandFactoryInterface $commandFactory
    ) {
        $this->commandFactory = $commandFactory;

        $parseCommand = $this->commandFactory->createCommand("parse");
        $this->initCommand = $parseCommand->execute();
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
            $nextCommand = $currentCommand->execute($this->initCommand);
            $this->initCommand = $nextCommand;
        }
    }
}
