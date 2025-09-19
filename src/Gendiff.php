<?php

namespace Differ;

use Differ\Formatters\StylishCommand;

function runGendiff(
    $commandFactory = new CommandFactory(
        new \Docopt(),
        new FileReader(),
        new StylishCommand()
    )
): void {
    $consoleApp = new ConsoleApp($commandFactory);
    try {
        $consoleApp->run();
    } catch (DifferException $e) {
        print_r("{$e->getMessage()}");
    }
}
