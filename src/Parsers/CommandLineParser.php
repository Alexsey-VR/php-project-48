<?php

namespace Differ\Parsers;

use Differ\Interfaces\CommandLineParserInterface;
use Differ\Interfaces\DocoptDoubleInterface;

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

        $filename = __DIR__ . "/../../docopt.txt";
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
        /**
         * @var \Docopt\Response|DocoptDoubleInterface $objArgs
         */
        $objArgs = $this->parser->handle($this->parserDescriptor, array('version' => '1.0.6'));
        if (isset($objArgs->args)) {
            if (is_array($objArgs->args)) {
                foreach ($objArgs->args as $key => $value) {
                    if (is_string($key)) {
                        $this->args[$key] = is_string($value) ? $value : "";
                    }
                }
            }
        } else {
            $this->args = [];
        }

        return $this;
    }

    /**
     * @param array<string,string> $fileNames
     */
    public function setFileNames(array $fileNames): CommandLineParserInterface
    {
        foreach ($fileNames as $key => $value) {
            $this->args[$key] = $value;
        }

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
