<?php

namespace Differ;

use Docopt;

class CommandLineParser implements CommandLineParserInterface
{
    private string $docopt;

    public function __construct(string $docopt)
    {
        $this->docopt = $docopt;
    }

    public function execute(): ?array
    {
        return (new Docopt())->handle($this->docopt, array('version' => '1.0.6'))
                             ->args;
    }
}
