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
        $currentFormat = strtolower($command->getFormat());
        $formatKeys = $commandFactory->getFormatKeys();
        if (in_array($currentFormat, $formatKeys)) {
            $this->formatCommand = $commandFactory->getCommand(
                $currentFormat
            );
        } else {
            throw new DifferException("input error: unknown output format\nUse gendiff -h\n");
        }

        return $this->formatCommand;
    }
}
