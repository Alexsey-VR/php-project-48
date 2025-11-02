<?php

namespace Differ\Differ;

use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\DisplayCommand;
use Differ\Formatters;
use Differ\DifferException;
use Differ\CommandLineParserInterface as CLPI;
use Differ\FilesDiffCommandInterface as FDCI;
use Differ\FormattersInterface as FI;
use Differ\DisplayCommandInterface as DCI;

function genDiff(
    string $pathToFile1,
    string $pathToFile2,
    string $format = 'stylish'
): string {
    $commandFactory = new CommandFactory(
        new \Docopt(),
        new FileReader(),
        new Formatters()
    );

    /**
     * @var CLPI $parseCommand
     */
    $parseCommand = $commandFactory->createCommand('parse');
    $fileNames = [
        "FILE1" => $pathToFile1,
        "FILE2" => $pathToFile2
    ];

    /**
     * @var CLPI $initCLPICommand
     */
    $initCLPICommand = $parseCommand->setFileNames($fileNames)
                                ->setFormat($format);

    $differ = $commandFactory->createCommand("difference");
    if ($differ instanceof FDCI) {
        $nextFDCICommand = $differ;
    } else {
        throw new DifferException("internal error: invalid type for \"difference\" command");
    }
    $initFDCICommand = $nextFDCICommand->execute($initCLPICommand);

    $formatter = $commandFactory->createCommand(strtolower($parseCommand->getFormat()));
    if ($formatter instanceof FI) {
        $nextFICommand = $formatter;
    } else {
        throw new DifferException("internal error: invalid type for \"format\" command");
    }
    $initFICommand = $nextFICommand->execute($initFDCICommand);

    $showCommand = $commandFactory->createCommand("show");
    if ($showCommand instanceof DCI) {
        $nextDCICommand = $showCommand;
    } else {
        throw new DifferException("internal error: invalid type for \"show\" command");
    }

    return $nextDCICommand->setFormatter($initFICommand)
                                    ->getFilesDiffs();
}
