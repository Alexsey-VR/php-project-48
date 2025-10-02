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
        array_reduce(
            $this->flowSteps,
            function ($nextCommand, $item) use ($commandFactory) {
                $currentCommand = $commandFactory->createCommand($item);
                $nextCommand = $currentCommand->execute($nextCommand);

                return $nextCommand;
            },
            $this->initCommand
        );
    }
}
