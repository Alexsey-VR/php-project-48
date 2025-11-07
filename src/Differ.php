<?php

namespace Differ\Differ;

function genDiff(
    string $pathToFile1,
    string $pathToFile2,
    string $format = 'stylish'
): string {
    $commandFactory = new \Differ\CommandFactory(
        new \Docopt(),
        new \Differ\FileReader(),
        new \Differ\Formatters()
    );

    /**
     * @var \Differ\Interfaces\CommandLineParserInterface $parseCommand
     */
    $parseCommand = $commandFactory->createCommand('parse');
    $fileNames = [
        "FILE1" => $pathToFile1,
        "FILE2" => $pathToFile2
    ];

    /**
     * @var \Differ\Interfaces\CommandLineParserInterface $initCLPICommand
     */
    $initCLPICommand = $parseCommand->setFileNames($fileNames)
                                ->setFormat($format);

    $differ = $commandFactory->createCommand("difference");
    if ($differ instanceof \Differ\Interfaces\FilesDiffCommandInterface) {
        $nextFDCICommand = $differ;
    } else {
        throw new \Differ\DifferException("internal error: invalid type for \"difference\" command");
    }
    $initFDCICommand = $nextFDCICommand->execute($initCLPICommand);

    $formatter = $commandFactory->createCommand(strtolower($parseCommand->getFormat()));
    if ($formatter instanceof \Differ\Interfaces\FormattersInterface) {
        $nextFICommand = $formatter;
    } else {
        throw new \Differ\DifferException("internal error: invalid type for \"format\" command");
    }
    $initFICommand = $nextFICommand->execute($initFDCICommand);

    $showCommand = $commandFactory->createCommand("show");
    if ($showCommand instanceof \Differ\Interfaces\DisplayCommandInterface) {
        $nextDCICommand = $showCommand;
    } else {
        throw new \Differ\DifferException("internal error: invalid type for \"show\" command");
    }

    return $nextDCICommand->setFormatter($initFICommand)
                                    ->getFilesDiffs();
}
