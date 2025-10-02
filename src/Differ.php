<?php

namespace Differ\Differ;

use Differ\CommandFactory;
use Differ\CommandLineParser;
use Differ\FilesDiffCommand;
use Differ\FileReader;
use Differ\DisplayCommand;
use Differ\Formatters;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish')
{
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
    $nextCommand = $parseCommand->setFileNames($fileNames)
                                ->setFormat($format);

    $differenceCommand = $commandFactory->createCommand('difference');
    $nextCommand = $differenceCommand->execute($nextCommand);

    $formatCommand = $commandFactory->createCommand(
        strtolower($parseCommand->getFormat())
    );
    $formatter = $formatCommand->execute($nextCommand);

    $displayCommand = $commandFactory->createCommand("show");
    return $displayCommand->setFormatter($formatter)
                                    ->getFilesDiffs();
}
