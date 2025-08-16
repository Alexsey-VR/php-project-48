<?php

namespace Differ\Differ;

use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\DisplayCommand;

function genDiff(string $pathToFile1, string $pathToFile2)
{
    $commandFactory = new CommandFactory();
    $parserCommand = $commandFactory->getCommand('parse');
    $fileNames = [
        "FILE1" => $pathToFile1,
        "FILE2" => $pathToFile2
    ];
    $nextCommand = $parserCommand->setFileNames($fileNames);

    $currentCommand = $commandFactory->getCommand('difference');
    $nextCommand = $currentCommand->setFileReader(new FileReader())
                                  ->execute($nextCommand);

    $currentCommand = $commandFactory->getCommand('show');
    return $currentCommand->execute($nextCommand);
}
