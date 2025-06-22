<?php

namespace Differ;

class ConsoleApp
{
    private string $docopt;
    private CommandFactoryInterface $commandFactory;
    private CommandLineParserInterface $commandLineParser;
    private CommandInterface $filesDiffCommand;
    private CommandInterface $displayCommand;
    private object $cliData;
    private object $filesContent;

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
        if ($this->commandLineParser = $this->commandFactory->getCommand("parse")) {
            $this->cliData = $this->commandLineParser->execute();
        } else {
            throw new \Exception("can't create command line parser");
        }

        if ($this->filesDiffCommand = $this->commandFactory->getCommand("difference")) {
            $this->filesContent = $this->filesDiffCommand->setFileReader(new FileReader())
                                                         ->execute($this->cliData);
        } else {
            throw new \Exception("can't create files difference command");
        }

        if ($this->displayCommand = $this->commandFactory->getCommand("show")) {
            $this->displayCommand->execute(/*(object)*/$this->filesContent)
                                 ->showDiffsToConsole();
        } else {
            throw new \Exception("can't create display command");
        }
    }
}
