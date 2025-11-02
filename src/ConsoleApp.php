<?php

namespace Differ;

use Differ\CommandLineParser;
use Differ\CommandInterface as CI;
use Differ\CommandLineParserInterface as CLPI;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\FormattersInterface as FI;
use Differ\DisplayCommandInterface as DCI;

class ConsoleApp
{
    private CLPI $parseCommand; 
    private CI|CLPI|FDCI|FI|DCI $nextCommand;
    private CommandFactoryInterface $commandFactory;

    /** 
     * @var array<string> $flowSteps
     */
    private array $flowSteps;
    private CI|CLPI|FDCI|FI|DCI $initCommand;

    public function __construct(
        CommandFactoryInterface $commandFactory
    ) {
        $this->commandFactory = $commandFactory;

        $this->parseCommand = $this->commandFactory->createCommand("parse");
        $this->initCommand = $this->parseCommand->execute($this->parseCommand);
        $this->flowSteps = [
            "difference",
            strtolower($this->parseCommand->getFormat()),
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
