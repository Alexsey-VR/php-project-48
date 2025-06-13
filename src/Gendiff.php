<?php

namespace App;

use Docopt;
use App\OutputInterface;
use App\CommandInterface;
use App\Invoker;
use App\Command;

function runGendiff(): void
{

    $doc = <<<'DOCOPT'
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

    $output = new class implements OutputInterface
    {
        public function parseCommandData(string $docopt): object
        {
            return Docopt::handle($docopt, array('version' => '1.0.6'));
        }
    };

    $command = new Command($output, $doc);
    $invoker = new Invoker();
    $invoker->setCommand($command);
    $invoker->run();
}
