<?php

namespace Differ;

function runGendiff(): void
{

    $docopt = <<<'DOCOPT'
    gendiff -h

    Generate diff

    Usage:
      gendiff (-h|--help)
      gendiff (-v|--version)
      gendiff [Options]... FILE1 FILE2

    Options:
      -h --help                  Show this screen
      -v --version               Show version
      --format <fmt>             Report format [default: stylish]

    DOCOPT;

    $output = new Output($docopt);
    $cliData = $output->parseCommandData();

    $filesDiffCommand = new FilesDiffCommand();
    $filesContent = $filesDiffCommand->setFileReader(new FileReader())
                                     ->execute($cliData);

    $displayCommand = new DisplayCommand();
    $displayCommand->execute((object)$filesContent)
                   ->showDiffsToConsole();
}
