<?php

namespace Differ;

interface CommandLineParserInterface
{
    public function execute(): ?array;
}
