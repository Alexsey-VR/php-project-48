<?php

namespace App;

interface OutputInterface
{
    public function parseCommandData(string $docopt): object;
}
