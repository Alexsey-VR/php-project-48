<?php

namespace Differ;

class ConsoleApp
{
    private string $docopt;
    private CommandInterface $currentCommand;
    private CommandInterface $nextCommand;
    private CommandFactoryInterface $commandFactory;
    private FileReaderInterface $fileReader;

    public function __construct(
        CommandFactoryInterface $commandFactory,
        FileReaderInterface $fileReader
    ) {
        $this->commandFactory = $commandFactory;
        $this->fileReader = $fileReader;
    }

    public function run(): void
    {
        $this->currentCommand = $this->commandFactory->getCommand("parse");
        $this->nextCommand = $this->currentCommand
                                  ->execute();

        $this->currentCommand = $this->commandFactory->getCommand("difference");
        $this->nextCommand = $this->currentCommand
                                  ->setFileReader($this->fileReader)
                                  ->execute($this->nextCommand);

        $this->currentCommand = $this->commandFactory->getCommand("show");
        $this->currentCommand
             ->execute($this->nextCommand);
    }
}
