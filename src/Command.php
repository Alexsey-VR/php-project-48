<?php

namespace App;

use App\CommandInterface;
use App\OutputInterface;

class Command implements CommandInterface
{
    protected $output;
    protected $docopt;
    protected $cliData;
    private const MAX_FILE_SIZE = 4096;

    public function __construct(OutputInterface $output, string $doc)
    {
        $this->output = $output;
        $this->docopt = $doc;
        $this->cliData = $this->output->parseCommandData($this->docopt);
    }

    private function parseFile(string $filename): array
    {
        $handle = fopen($filename, "r");
        $result = json_decode(fread($handle, self::MAX_FILE_SIZE));
        fclose($handle);
        $type = gettype($result);
        if ($type === 'object') {
            $keys = (get_object_vars($result));
        } elseif ($type === 'array') {
            $keys = array_keys($result);
        } else {
            $keys = null;
        }

        return $keys;
    }

    public function execute()
    {
        if (isset($this->cliData['FILE1']) & isset($this->cliData['FILE2'])) {
            if (file_exists($this->cliData['FILE1']) & file_exists($this->cliData['FILE2'])) {
                $file1Content = $this->parseFile($this->cliData['FILE1']);
                $file2Content = $this->parseFile($this->cliData['FILE2']);

                print_r("File1 content:\n");
                print_r($file1Content);
                print_r("File2 content:\n");
                print_r($file2Content);
            } else {
                print_r("File {$this->cliData['FILE2']} not exists\n");
            }
        } else {
            print_r("File {$this->cliData['FILE1']} not exists\n");
        }
    }
}
