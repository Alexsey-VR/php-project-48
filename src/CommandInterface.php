<?php

namespace Differ;

interface CommandInterface
{
    public function execute(CommandInterface $data = null): CommandInterface;
}
