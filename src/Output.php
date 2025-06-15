<?php

namespace App;

use App\OutputInterface;
use Docopt;

class Output implements OutputInterface
{
    private string $docopt;

    public function __construct(string $docopt)
    {
        $this->docopt = $docopt;
    }

    public function parseCommandData(): object
    {
        return (new Docopt)->handle($this->docopt, array('version' => '1.0.6'));
    }
}
