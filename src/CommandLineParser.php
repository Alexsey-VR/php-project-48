<?php

namespace Differ;

class CommandLineParser implements CommandInterface
{
    private string $parserDescriptor;
    private mixed $parser;
    private array $args;
    private string $defaultFormat;

    public function __construct(mixed $parser = null)
    {
        $this->parser = $parser;
        $this->defaultFormat = 'stylish';

        $filename = __DIR__ . "/../docopt.txt";
        $handler = @fopen($filename, 'r');
        $filesize = filesize($filename);
        if (($handler !== false) && ($filesize > 0)) {
            $fileData = fread($handler, $filesize);
            if (is_string($fileData)) {
                $this->parserDescriptor = $fileData;
            }
            fclose($handler);
        }
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
        $this->args = array_reduce(
            array_keys($fileNames),
            function ($args, $key) use ($fileNames) {
                $args[$key] = $fileNames[$key];

                return $args;
            },
            []
        );

        return $this;
    }

    public function getFileNames(): array
    {
        return $this->args;
    }

    public function setFormat(string $format): CommandInterface
    {
        $this->defaultFormat = $format;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->args['--format'] ?? $this->defaultFormat;
    }
}
