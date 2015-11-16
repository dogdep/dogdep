<?php namespace App\Commands;

use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Command
{
    use DispatchesJobs;

    /**
     * @var Command[]
     */
    protected $commands = [];

    /**
     * @param Command|Command[] $command
     */
    public function addNextCommand($command)
    {
        if (is_array($command)) {
            foreach ($command as $item) {
                $this->commands[] = $item;
            }
        } else {
            $this->commands[] = $command;
        }
    }

    public function handlingCommandFinished()
    {
        if (!$this->commands) {
            return;
        }
        $command = array_shift($this->commands);
        $command->addNextCommand($this->commands);
        $this->dispatch($command);
    }
}
