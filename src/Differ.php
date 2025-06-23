<?php

namespace Differ\Differ;

use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\DisplayCommand;

function genDiff(string $pathToFile1, string $pathToFile2)
{
    $parserCommand = new CommandLineParser();
    $fileNames = [
        "FILE1" => $pathToFile1,
        "FILE2" => $pathToFile2
    ];
    $nextCommand = $parserCommand->setFileNames($fileNames);

    $currentCommand = new FilesDiffCommand();
    $nextCommand = $currentCommand->getFileNames($nextCommand)
                                  ->setFileReader(new FileReader())
                                  ->execute();

    $currentCommand = new DisplayCommand();
    return $currentCommand->execute($nextCommand)
                          ->getDiffs();
}
