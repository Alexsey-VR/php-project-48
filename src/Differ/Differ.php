<?php

namespace Differ\Differ;

use Docopt;
use Differ\Interfaces\CommandFactoryInterface;
use Differ\Interfaces\FilesDiffCommandInterface;
use Differ\Interfaces\FileParserInterface;
use Differ\Interfaces\CommandLineParserInterface;
use Differ\Interfaces\FormattersInterface;
use Differ\Interfaces\DisplayCommandInterface;
use Differ\Factories\CommandFactory;
use Differ\Readers\FileReader;
use Differ\Factories\Formatters;
use Differ\Exceptions\DifferException;
use Differ\Parsers\DocoptDouble;

class Differ
{
    private CommandLineParserInterface $parseCommand;
    private FilesDiffCommandInterface $nextFDCICommand;
    private FormattersInterface $nextFICommand;
    private DisplayCommandInterface $nextDCICommand;
    private CommandFactoryInterface $commandFactory;
    private CommandLineParserInterface $initCLPICommand;
    private FilesDiffCommandInterface $initFDCICommand;
    private FormattersInterface $initFICommand;

    public function __construct(CommandFactoryInterface $commandFactory)
    {
        $this->commandFactory = $commandFactory;

        $parser = $this->commandFactory->createCommand("parseCMDLine");
        if ($parser instanceof CommandLineParserInterface) {
            $this->parseCommand = $parser;
        } else {
            throw new DifferException("internal error: invalid type for \"parseCMDLine\" command");
        }
        $this->initCLPICommand = $this->parseCommand->execute($this->parseCommand);
    }

    public function run(): void
    {
        $differ = $this->commandFactory->createCommand("difference");
        if ($differ instanceof FilesDiffCommandInterface) {
            $this->nextFDCICommand = $differ;
        } else {
            throw new DifferException("internal error: invalid type for \"difference\" command");
        }
        $fileParser = $this->commandFactory->createCommand("parseFile");
        if ($fileParser instanceof FileParserInterface) {
            $this->initFDCICommand = $this->nextFDCICommand->execute(
                $this->initCLPICommand,
                $fileParser
            );
        } else {
            throw new DifferException("internal error: invalid type for \"parseFile\" command");
        }

        $formatter = $this->commandFactory->createCommand(strtolower($this->parseCommand->getFormat()));
        if ($formatter instanceof FormattersInterface) {
            $this->nextFICommand = $formatter;
        } else {
            throw new DifferException("internal error: invalid type for \"format\" command");
        }
        $this->initFICommand = $this->nextFICommand->execute($this->initFDCICommand);

        $showCommand = $this->commandFactory->createCommand("show");
        if ($showCommand instanceof DisplayCommandInterface) {
            $this->nextDCICommand = $showCommand;
        } else {
            throw new DifferException("internal error: invalid type for \"show\" command");
        }
        $this->nextDCICommand->execute($this->initFICommand);
    }

    public function genDiff(
        string $pathToFile1,
        string $pathToFile2,
        string $format = 'stylish'
    ): string {
        $commandFactory = new CommandFactory(
            new Docopt(),
            new FileReader(),
            new Formatters()
        );

        /**
         * @var CommandLineParserInterface $parseCommand
         */
        $parseCommand = $commandFactory->createCommand('parseCMDLine');
        $fileNames = [
            "FILE1" => $pathToFile1,
            "FILE2" => $pathToFile2
        ];

        /**
         * @var CommandLineParserInterface $initCLPICommand
         */
        $initCLPICommand = $parseCommand->setFileNames($fileNames)
                                    ->setFormat($format);

        $differ = $commandFactory->createCommand("difference");
        if ($differ instanceof FilesDiffCommandInterface) {
            $nextFDCICommand = $differ;
        } else {
            throw new DifferException("internal error: invalid type for \"difference\" command");
        }
        $fileParser = $commandFactory->createCommand("parseFile");
        if ($fileParser instanceof FileParserInterface) {
            $initFDCICommand = $nextFDCICommand->execute($initCLPICommand, $fileParser);
        } else {
            throw new DifferException("internal error: invalid type for \"parseFile\" command");
        }

        $formatter = $commandFactory->createCommand(strtolower($parseCommand->getFormat()));
        if ($formatter instanceof FormattersInterface) {
            $nextFICommand = $formatter;
        } else {
            throw new DifferException("internal error: invalid type for \"format\" command");
        }
        $initFICommand = $nextFICommand->execute($initFDCICommand);

        $showCommand = $commandFactory->createCommand("show");
        if ($showCommand instanceof DisplayCommandInterface) {
            $nextDCICommand = $showCommand;
        } else {
            throw new DifferException("internal error: invalid type for \"show\" command");
        }

        return $nextDCICommand->setFormatter($initFICommand)
                                        ->getFilesDiffs();
    }
}

function genDiff(
    string $pathToFile1,
    string $pathToFile2,
    string $format = 'stylish'
): string {
    $differ = new Differ(
        new CommandFactory(
            new DocoptDouble(),
            new FileReader(),
            new Formatters()
        )
    );

    return $differ->genDiff(
        $pathToFile1,
        $pathToFile2,
        $format
    );
}
