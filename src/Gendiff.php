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

    $commandFactory = new CommandFactory($docopt);
    $fileReader = new FileReader();

    $consoleApp = new ConsoleApp($commandFactory, $fileReader);
    $consoleApp->run();
}
