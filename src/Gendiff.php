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

    $factory = new CommandFactory($docopt);

    $outputParser = $factory->getCommand("parse");
    if (is_null($outputParser)) {
        print_r("error: can't create command to parse command data\n");
    } else {
        $cliData = $outputParser->parseCommandData();
    }

    $filesDiffCommand = $factory->getCommand("difference");
    if (is_null($filesDiffCommand)) {
        print_r("error: can't create command to get files difference\n");
        exit;
    } else {
        $filesContent = $filesDiffCommand->setFileReader(new FileReader())
                                         ->execute($cliData);
    }

    $displayCommand = $factory->getCommand("show");
    if (is_null($displayCommand)) {
        print_r("error: can't create command to show differences to console\n");
        exit;
    } else {
        $displayCommand->execute((object)$filesContent)
                       ->showDiffsToConsole();
    }
}
