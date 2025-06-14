<?php

namespace App;

use App\CommandInterface;
use App\OutputInterface;

class ViewFilesCommand implements CommandInterface
{
    protected $output;
    private const MAX_FILE_SIZE = 4096;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
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

    public function execute(object $cliData)
    {
        if (isset($cliData['FILE1']) && isset($cliData['FILE2'])) {
            if (file_exists($cliData['FILE1']) && file_exists($cliData['FILE2'])) {
                $file1Content = $this->parseFile($cliData['FILE1']);
                $file2Content = $this->parseFile($cliData['FILE2']);

                print_r("File1 content:\n");
                print_r($file1Content);
                print_r("File2 content:\n");
                print_r($file2Content);
            } else {
                print_r("File {$cliData['FILE1']} or {$cliData['FILE2']} not exists\n");
            }
        }
    }
}
