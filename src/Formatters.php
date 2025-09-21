<?php

namespace Differ;

class Formatters implements CommandInterface
{
    private CommandInterface $formatCommand;

    public function execute(CommandInterface $command = null): CommandInterface
    {
        $commandFactory = new CommandFactory(
            new \Docopt(),
            new FileReader()
        );
        $this->formatCommand = $commandFactory->getCommand(
            $command->getFormat()
        );

        return $this->formatCommand;
    }
}
