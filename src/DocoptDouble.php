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
            "FILE1" => __DIR__ . "/../fixtures/file1.json",
            "FILE2" => __DIR__ . "/../fixtures/file2.json"
        ];
    }

    public function handle()
    {
        return $this;
    }
}
