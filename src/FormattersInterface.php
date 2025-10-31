<?php

namespace Differ;

interface FormattersInterface
{
    public function execute(FilesDiffCommandInterface $command): FormattersInterface;
    public function getContentString(): string;
    public function getDiffsString(): string;
}
