<?php

namespace Differ;

class ConsoleApp
{
    private string $docopt;
    private CommandInterface $currentCommand;
    private CommandInterface $nextCommand;
    private CommandFactoryInterface $commandFactory;

    public function __construct()
    {
        $this->docopt = <<<'DOCOPT'
        gendiff -h

        Generate diff

        Usage:
          gendiff (-h|--help)
          gendiff (-v|--version)
          gendiff [Options]... FILE1 FILE2

        Options:
          -h --help                  Show this screen
          -v --version               Show version
          --format <fmt>             Report format [default: stylish]

        DOCOPT;

        $this->commandFactory = new CommandFactory($this->docopt);
    }

    public function run(): void
    {
        $this->currentCommand = $this->commandFactory->getCommand("parse");
        $this->nextCommand = $this->currentCommand
                                  ->execute();

        $this->currentCommand = $this->commandFactory->getCommand("difference");
        $this->nextCommand = $this->currentCommand
                                  ->setFileNames($this->nextCommand)
                                  ->setFileReader(new FileReader())
                                  ->execute();

        $this->currentCommand = $this->commandFactory->getCommand("show");
        $this->currentCommand
             ->execute($this->nextCommand)
             ->showDiffsToConsole();
    }
}
