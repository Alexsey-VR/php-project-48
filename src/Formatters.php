<?php

namespace Differ;

class Formatters implements CommandInterface
{
    private CommandInterface $formatCommand;

    public function selectFormat(CommandInterface $command = null): CommandInterface
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

        return $this;
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        $this->formatCommand = $this->formatCommand->execute($command);

        return $this;
    }

    public function getFilesContent(): string
    {
        return $this->formatCommand->filesContentString;
    }

    public function getFilesDiffs(): string
    {
        return $this->formatCommand->filesDiffsString;
    }
}
