<?php

namespace Differ\Differ;

use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\DisplayCommand;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish')
{
    $commandFactory = new CommandFactory(
        new \Docopt(),
        new FileReader()
    );
    $parseCommand = $commandFactory->getCommand('parse');
    $fileNames = [
        "FILE1" => $pathToFile1,
        "FILE2" => $pathToFile2
    ];
    $nextCommand = $parseCommand->setFileNames($fileNames)
                                ->setFormat($format);

    $differenceCommand = $commandFactory->getCommand('difference');
    $nextCommand = $differenceCommand->execute($nextCommand);

    $formatCommand = $commandFactory->getCommand('format');
    $nextCommand = $formatCommand->selectFormat($parseCommand)
                                ->execute($nextCommand);

    return $nextCommand->getFilesDiffs();
}
