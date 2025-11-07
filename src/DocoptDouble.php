<?php

namespace Differ;

class DocoptDouble implements \Differ\Interfaces\DocoptDoubleInterface
{
    /**
     * @var array<string,string>
     */
    public array $args;

    public function __construct(string $format = "stylish")
    {
        $this->args = [
            "--help" => "",
            "--version" => "",
            "--format" => $format,
            "FILE1" => __DIR__ . "/../fixtures/file1.json",
            "FILE2" => __DIR__ . "/../fixtures/file2.json"
        ];
    }

    public function handle(): \Differ\Interfaces\DocoptDoubleInterface
    {
        return $this;
    }
}
