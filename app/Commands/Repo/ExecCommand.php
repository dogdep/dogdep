<?php namespace App\Commands\Repo;

use App\Commands\Command as BaseCommand;
use App\Model\Command;
use App\Model\Release;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * Class ExecCommand
 */
class ExecCommand extends BaseCommand implements ShouldBeQueued, SelfHandling
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var Release
     */
    private $release;

    /**
     * @param Command $command
     * @param Release $release
     */
    function __construct(Command $command, Release $release)
    {
        $this->command = $command;
        $this->release = $release;
    }

    public function handle()
    {
        $this->command->get($this->release)->run();
    }
}
