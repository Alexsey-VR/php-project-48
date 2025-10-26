<?php

namespace Differ;

class CommandLineParser implements CommandLineParserInterface
{
    private string $parserDescriptor;
    private \Docopt|DocoptDoubleInterface $parser;

    /**
     * @var array<string,string> $args
     */
    private array $args;
    
    private string $defaultFormat;

    public function __construct(\Docopt|DocoptDoubleInterface $parser)
    {
        $this->parser = $parser;
        $this->defaultFormat = 'stylish';

        $filename = __DIR__ . "/../docopt.txt";
        if (file_exists($filename)) {
            $handler = fopen($filename, 'r');
            $filesize = filesize($filename);
            if ($handler !== false) {
                $fileData = fread(
                    $handler,
                    ($filesize !== false) ? max(1, $filesize) : 1
                );
                $this->parserDescriptor = is_string($fileData) ? $fileData : "";
                fclose($handler);
            }
        }
    }

    public function execute(CommandLineParserInterface $command): CommandLineParserInterface
    {
        $objArgs = $this->parser->handle($this->parserDescriptor, array('version' => '1.0.6'));
        $this->args = $objArgs->args;

        return $this;
    }

    /**
     * @param array<string,string> $fileNames
     */
    public function setFileNames(array $fileNames): CommandLineParserInterface
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

    /**
     * @return array<string,string>
     */
    public function getFileNames(): array
    {
        return $this->args;
    }

    public function setFormat(string $format): CommandLineParserInterface
    {
        $this->defaultFormat = $format;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->args['--format'] ?? $this->defaultFormat;
    }
}
