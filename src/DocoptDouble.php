<?php

namespace Differ;

class DocoptDouble
{
    public array $args;

    public function __construct(string $format = "stylish")
    {
        $this->args = [
            "--help" => null,
            "--version" => null,
            "--format" => $format,
            "FILE1" => __DIR__ . "/../fixtures/file1.json",
            "FILE2" => __DIR__ . "/../fixtures/file2.json"
        ];
    }

    public function handle()
    {
        return $this;
    }
}
