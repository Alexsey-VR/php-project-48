<?php

namespace Differ;

interface FormattersInterface
{
    public function execute(CommandInterface $command): FormattersInterface;
    public function getContentString(): string;
    public function getDiffsString(): string;
}
