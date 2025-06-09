<?php

namespace App;

use Docopt;

function runGendiff()
{
    $doc = <<<'DOCOPT'
    gendiff -h

    Generate diff

    Usage:
      gendiff (-h|--help)
      gendiff (-v|--version)

    Options:
      -h --help                  Show this screen
      -v --version               Show version
      --format <fmt>             Report format [default: stylish]

    DOCOPT;

    $result = Docopt::handle($doc, array('version' => '1.0.6'));

    foreach ($result as $k => $v) {
        print_r($k . ': ' . json_encode($v) . PHP_EOL);
    }
}
