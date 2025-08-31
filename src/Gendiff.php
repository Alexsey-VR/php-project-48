<?php

namespace Differ;

function runGendiff(
    $commandFactory = new CommandFactory(
        new \Docopt(),
        new FileReader()
    )
): void {
    $consoleApp = new ConsoleApp($commandFactory);
    try {
        $consoleApp->run();
    } catch (DifferException $e) {
        print_r("{$e->getMessage()}");
    }
}
