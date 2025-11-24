<?php

namespace Differ\Tests\Fixtures;

use Differ\Interfaces\DocoptDoubleInterface;

class DocoptDouble implements DocoptDoubleInterface
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
            "FILE1" => $_ENV["FIXTURES_PATH"] . "/file1.json",
            "FILE2" => $_ENV["FIXTURES_PATH"] . "/file2.json"
        ];
    }

    public function handle(): DocoptDoubleInterface
    {
        return $this;
    }
}
