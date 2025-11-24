<?php

namespace Differ\Parsers;

use Differ\Interfaces\DocoptDoubleInterface;

class DocoptDouble implements DocoptDoubleInterface
{
    /**
     * @var array<string,string>
     */
    public array $args;

    public function __construct(
        string $file1Path = "",
        string $file2Path = "",
        string $format = "stylish"
    ) {
        $this->args = [
            "--help" => "",
            "--version" => "",
            "--format" => $format,
            "FILE1" => $file1Path,
            "FILE2" => $file2Path
        ];
    }

    public function handle(): DocoptDoubleInterface
    {
        return $this;
    }
}
