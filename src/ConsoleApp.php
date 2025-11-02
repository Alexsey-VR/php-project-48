<?php

namespace Differ;

use Differ\CommandFactoryInterface as CFI;
use Differ\CommandLineParserInterface as CLPI;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\FormattersInterface as FI;
use Differ\DisplayCommandInterface as DCI;

class ConsoleApp
{
    private CLPI $parseCommand;
    private FDCI $nextFDCICommand;
    private FI $nextFICommand;
    private DCI $nextDCICommand;
    private CFI $commandFactory;
    private CLPI $initCLPICommand;
    private FDCI $initFDCICommand;
    private FI $initFICommand;

    public function __construct(
        CommandFactoryInterface $commandFactory
    ) {
        $this->commandFactory = $commandFactory;

        $parser = $this->commandFactory->createCommand("parse");
        if ($parser instanceof CLPI) {
            $this->parseCommand = $parser;
        } else {
            throw new DifferException("internal error: invalid type for \"parse\" command");
        }
        $this->initCLPICommand = $this->parseCommand->execute($this->parseCommand);
    }

    public function run(): void
    {
        $differ = $this->commandFactory->createCommand("difference");
        if ($differ instanceof FDCI) {
            $this->nextFDCICommand = $differ;
        } else {
            throw new DifferException("internal error: invalid type for \"difference\" command");
        }
        $this->initFDCICommand = $this->nextFDCICommand->execute($this->initCLPICommand);

        $formatter = $this->commandFactory->createCommand(strtolower($this->parseCommand->getFormat()));
        if ($formatter instanceof FI) {
            $this->nextFICommand = $formatter;
        } else {
            throw new DifferException("internal error: invalid type for \"format\" command");
        }
        $this->initFICommand = $this->nextFICommand->execute($this->initFDCICommand);

        $showCommand = $this->commandFactory->createCommand("show");
        if ($showCommand instanceof DCI) {
            $this->nextDCICommand = $showCommand;
        } else {
            throw new DifferException("internal error: invalid type for \"show\" command");
        }
        $this->nextDCICommand->execute($this->initFICommand);
    }
}
