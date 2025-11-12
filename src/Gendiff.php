<?php

namespace Differ;

/**
 * @param \Differ\Interfaces\CommandFactoryInterface $commandFactory
 */
function runGendiff(
    $commandFactory = new \Differ\Factories\CommandFactory(
        new \Docopt(),
        new FileReader(),
        new \Differ\Factories\Formatters()
    )
): void {
    $consoleApp = new ConsoleApp($commandFactory);
    try {
        $consoleApp->run();
    } catch (DifferException $e) {
        print_r("{$e->getMessage()}");
    }
}
