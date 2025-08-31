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
    } catch (\Exception $e) {
        print_r("{$e->getMessage()}");
    }
}
