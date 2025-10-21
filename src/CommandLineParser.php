<?php

namespace Differ;

class CommandLineParser implements CommandLineParserInterface
{
    private string $parserDescriptor;
    private \Docopt|DocoptDoubleInterface $parser;

    /**
     * @var array<string,string>
     */
    private array $args;
    private string $defaultFormat;

    public function __construct(\Docopt|DocoptDoubleInterface $initParser)
    {
        $this->parser = $initParser;
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

    /**
     * @return array<string,string>
     * @param mixed $args
     */
    private function getDocoptArgs(mixed $args): array
    {
        $keys = is_array($args) ? array_keys($args) : ["key" => "value"];
        $values = is_array($args) ? array_values($args) : ["key" => "value"];

        /**
         * @var array<string,string>
         */
        $result = array_reduce(
            $keys,
            function ($accum, $key) use ($keys, $values): array {
                $id = array_search($key, $keys);
                if ($id !== false) {
                    $accum[$keys[$id]] = $values[$id];
                }
                return $accum;
            },
            [$keys[0] => $values[0]]
        );

        return $result;
    }

    public function execute(CommandLineParserInterface $command): CommandLineParserInterface
    {
        $objArgs = $this->parser->handle($this->parserDescriptor, array('version' => '1.0.6'));

        if (isset($objArgs->args)) {
            $this->args = $this->getDocoptArgs($objArgs->args);
        }

        return $this;
    }

    /**
     * @param array<string,string> $fileNames
     */
    public function setFileNames(array $fileNames): CommandLineParserInterface
    {
        $result = [];
        foreach ($fileNames as $key => $value) {
            $result[$key] = $value;
        }

        $this->args = $result;

        return $this;
    }

    /**
     * @return array<string,string>
     */
    public function getFileNames(): array
    {
        return $this->args;
    }

    /**
     * @return CommandLineParserInterface
     */
    public function setFormat(string $format): CommandLineParserInterface
    {
        $this->defaultFormat = $format;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->args['--format'] ?? $this->defaultFormat;
    }
}
