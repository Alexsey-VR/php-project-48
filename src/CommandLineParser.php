<?php

namespace Differ;

class CommandLineParser implements CommandLineParserInterface
{
    private string $parserDescriptor;
    private \Docopt $parser;
    /**
     * @var array<string,string> $args
     */
    private array $args;
    private string $defaultFormat;

    public function __construct(\Docopt $initParser)
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
     * @param array<mixed,mixed> $args
     */
    private function getDocoptArgs(array $args): array
    {
        $result = [];
        foreach ($args as $key => $arg) {
            if (is_string($key) && is_string($arg)) {
                $result[$key] = $arg;
            }
        }

        return $result;
    }

    /**
     * @return CommandLineParserInterface
     */
    public function execute(CommandLineParserInterface $command): CommandLineParserInterface
    {
        $objArgs = $this->parser->handle($this->parserDescriptor, array('version' => '1.0.6'));

        $this->args = $this->getDocoptArgs($objArgs->args);

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
