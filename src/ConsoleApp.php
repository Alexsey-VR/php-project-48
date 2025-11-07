<?php

namespace Differ;

class ConsoleApp
{
    private \Differ\Interfaces\CommandLineParserInterface $parseCommand;
    private \Differ\Interfaces\FilesDiffCommandInterface $nextFDCICommand;
    private \Differ\Interfaces\FormattersInterface $nextFICommand;
    private \Differ\Interfaces\DisplayCommandInterface $nextDCICommand;
    private \Differ\Interfaces\CommandFactoryInterface $commandFactory;
    private \Differ\Interfaces\CommandLineParserInterface $initCLPICommand;
    private \Differ\Interfaces\FilesDiffCommandInterface $initFDCICommand;
    private \Differ\Interfaces\FormattersInterface $initFICommand;

    public function __construct(
        \Differ\Interfaces\CommandFactoryInterface $commandFactory
    ) {
        $this->commandFactory = $commandFactory;

        $parser = $this->commandFactory->createCommand("parse");
        if ($parser instanceof \Differ\Interfaces\CommandLineParserInterface) {
            $this->parseCommand = $parser;
        } else {
            throw new DifferException("internal error: invalid type for \"parse\" command");
        }
        $this->initCLPICommand = $this->parseCommand->execute($this->parseCommand);
    }

    public function run(): void
    {
        $differ = $this->commandFactory->createCommand("difference");
        if ($differ instanceof \Differ\Interfaces\FilesDiffCommandInterface) {
            $this->nextFDCICommand = $differ;
        } else {
            throw new DifferException("internal error: invalid type for \"difference\" command");
        }
        $this->initFDCICommand = $this->nextFDCICommand->execute($this->initCLPICommand);

        $formatter = $this->commandFactory->createCommand(strtolower($this->parseCommand->getFormat()));
        if ($formatter instanceof \Differ\Interfaces\FormattersInterface) {
            $this->nextFICommand = $formatter;
        } else {
            throw new DifferException("internal error: invalid type for \"format\" command");
        }
        $this->initFICommand = $this->nextFICommand->execute($this->initFDCICommand);

        $showCommand = $this->commandFactory->createCommand("show");
        if ($showCommand instanceof \Differ\Interfaces\DisplayCommandInterface) {
            $this->nextDCICommand = $showCommand;
        } else {
            throw new DifferException("internal error: invalid type for \"show\" command");
        }
        $this->nextDCICommand->execute($this->initFICommand);
    }
}
