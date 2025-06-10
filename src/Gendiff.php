<?php

namespace App;

use Docopt;

function parseFile(string $filename): array
{
    $handle = fopen($filename, "r");
    $result = json_decode(fread($handle, 4096));
    fclose($handle);

    $keys = (get_object_vars($result));

    return $keys;
}

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

    $cliData = Docopt::handle($doc, array('version' => '1.0.6'));
/*
    foreach ($cliData as $k => $v) {
        print_r($k . ': ' . json_encode($v) . PHP_EOL);
    }
*/
    if (file_exists($cliData['FILE1'])) {
        if (file_exists($cliData['FILE2'])) {
            $file1Content = parseFile($cliData['FILE1']);
            $file2Content = parseFile($cliData['FILE2']);

            print_r("File1 content:\n");
            print_r($file1Content);
            print_r("File2 content:\n");
            print_r($file2Content);
        } else {
            print_r("File {$cliData['FILE2']} not exists");
        }
    } else {
        print_r("File {$cliData['FILE1']} not exists\n");
        runGendiff();
    }
}
