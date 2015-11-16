<?php namespace App\Commands\Pipeline;

use App\Commands\Command;

/**
 * Class ChainCommands
 */
class ChainCommands
{
    /**
     * @param Command $command
     * @param Callable $next
     * @return mixed
     */
    public function handle(Command $command, $next)
    {
        $result = $next($command);
        $command->handlingCommandFinished();

        return $result;
    }
}
