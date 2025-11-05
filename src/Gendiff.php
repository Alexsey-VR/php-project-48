<?php

namespace Differ;

/**
 * @param CommandFactoryInterface $commandFactory
 */
function runGendiff(
    $commandFactory = new CommandFactory(
        new \Docopt(),
        new FileReader(),
        new Formatters()
    )
): void {
    $consoleApp = new ConsoleApp($commandFactory);
    try {
        $consoleApp->run();
    } catch (DifferException $e) {
        print_r("{$e->getMessage()}");
    }
}
