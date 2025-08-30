<?php

namespace Differ;

class CommandLineParser implements CommandInterface
{
    private string $parserDescriptor;
    private $parser;
    private array $args;

    public function __construct($parser = null)
    {
        $this->parser = $parser;

        $filename = __DIR__ . "/../docopt.txt";
        $handler = @fopen($filename, 'r');
        $filesize = filesize($filename);
        $this->parserDescriptor = fread($handler, $filesize);
        fclose($handler);
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        if (is_null($command)) {
            $objArgs = $this->parser->handle($this->parserDescriptor, array('version' => '1.0.6'));
            $this->args = $objArgs->args;
        }

        return $this;
    }

    public function setFileNames(array $fileNames): CommandInterface
    {
        $keys = array_keys($fileNames);
        foreach ($keys as $key) {
            $this->args[$key] = $fileNames[$key];
        }
        return $this;
    }

    public function getFileNames(): array
    {
        return $this->args;
    }
}
