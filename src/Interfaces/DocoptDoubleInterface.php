<?php

namespace Differ\Interfaces;

interface DocoptDoubleInterface
{
    public function handle(): \Docopt\Response|DocoptDoubleInterface;
}
