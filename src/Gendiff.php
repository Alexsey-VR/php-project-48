<?php

namespace Differ;

function runGendiff(
    $commandFactory = new CommandFactory(
        new \Docopt(),
        new FileReader()
    )
): void {
    $consoleApp = new ConsoleApp($commandFactory);
    $consoleApp->run();
}
