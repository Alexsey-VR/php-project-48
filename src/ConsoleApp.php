<?php

namespace Differ;

class ConsoleApp
{
    private string $docopt;
    private array $commandTypeList;
    private $currentCommand;
    private array $cliData;
    private array $filesContent;
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
        $this->commandTypeList = $this->commandFactory->getCommandTypeList();
    }

    public function run(): void
    {
        foreach ($this->commandTypeList as $commandType) {
            $this->currentCommand = $this->commandFactory->getCommand($commandType);
            switch ($commandType) {
                case "parse":
                    $this->cliData = $this->currentCommand->execute();
                    break;
                case "difference":
                    $this->currentCommand->setFileReader(new FileReader());
                    $this->filesContent = $this->currentCommand->execute($this->cliData);
                    break;
                case "show":
                    $this->currentCommand->execute($this->filesContent);
                    $this->currentCommand->showDiffsToConsole();
                    break;
                default:
                    throw new \Exception('unknown command type');
            }
        }
    }
}
