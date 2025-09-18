<?php

namespace Differ\Differ;

use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\StylishCommand;
use Differ\DisplayCommand;

function genDiff(string $pathToFile1, string $pathToFile2)
{
    $commandFactory = new CommandFactory(
        new \Docopt(),
        new FileReader(),
        new StylishCommand()
    );
    $parserCommand = $commandFactory->getCommand('parse');
    $fileNames = [
        "FILE1" => $pathToFile1,
        "FILE2" => $pathToFile2
    ];
    $nextCommand = $parserCommand->setFileNames($fileNames);

    $currentCommand = $commandFactory->getCommand('difference');
    $nextCommand = $currentCommand->execute($nextCommand);

    $currentCommand = $commandFactory->getCommand('stylish');
    $nextCommand = $currentCommand->execute($nextCommand);

    $currentCommand = $commandFactory->getCommand('show');
    return $currentCommand->execute($nextCommand);
}
