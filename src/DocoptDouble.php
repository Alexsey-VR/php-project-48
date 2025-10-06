<?php

namespace Differ;

class DocoptDouble
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

    /**
     * @return mixed
     */
    public function handle()
    {
        return $this;
    }
}
