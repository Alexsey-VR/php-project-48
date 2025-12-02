<?php

namespace Differ\Interfaces;

use Docopt\Response;

interface DocoptDoubleInterface
{
    public function handle(): Response|DocoptDoubleInterface;
}
