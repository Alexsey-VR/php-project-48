<?php

namespace Differ;

interface DocoptDoubleInterface
{
    public function handle(): \Docopt\Response|DocoptDoubleInterface;
}