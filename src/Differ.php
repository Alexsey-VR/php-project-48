<?php

namespace Differ\Differ;

use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\DisplayCommand;
use Differ\Formatters;

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
    $parseCommand = $commandFactory->createCommand('parse');
    $fileNames = [
        "FILE1" => $pathToFile1,
        "FILE2" => $pathToFile2
    ];

    $initCommand = $parseCommand->setFileNames($fileNames)
                                ->setFormat($format);
    $flowSteps = [
        "difference",
        strtolower($parseCommand->getFormat())
    ];
    foreach ($flowSteps as $step) {
        $currentCommand = $commandFactory->createCommand($step);
        $nextCommand = $currentCommand->execute($initCommand);
        $initCommand = $nextCommand;
    }
    $displayCommand = $commandFactory->createCommand("show");

    return $displayCommand->setFormatter($initCommand)
                                    ->getFilesDiffs();
}
