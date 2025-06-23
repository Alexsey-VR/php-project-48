<?php

namespace Differ;

use Docopt;

class CommandLineParser implements CommandInterface
{
    private string $docopt;
    private array $args;

    public function __construct(string $docopt = "")
    {
        $this->docopt = $docopt;
    }

    public function execute(CommandInterface $command = null): CommandInterface
    {
        $this->args = (new Docopt())->handle($this->docopt, array('version' => '1.0.6'))
                                    ->args;
        return $this;
    }

    public function setFileNames(array $fileNames): CommandInterface
    {
        $this->args["FILE1"] = $fileNames["FILE1"];
        $this->args["FILE2"] = $fileNames["FILE2"];

        return $this;
    }

    public function getFileNames()
    {
        return $this->args;
    }
}
