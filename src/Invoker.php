<?php

namespace App;

use App\CommandInterface;

class Invoker
{
    private $command;

    public function setCommand(CommandInterface $command)
    {
        $this->command = $command;
    }

    public function run()
    {
        $this->command->execute();
    }
}
