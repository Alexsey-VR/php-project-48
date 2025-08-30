<?php

namespace Differ;

class DocoptDouble
{
    public array $args;

    public function __construct()
    {
        $this->args = [
            "--help" => null,
            "--version" => null,
            "Options" => 0,
            "FILE1" => "file1.json",
            "FILE2" => "file2.json"
        ];
    }

    public function handle(string $data, array $default)
    {
        return $this;
    }
}
