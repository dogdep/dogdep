<?php namespace App\Commands\Repo;

use App\Commands\Command;
use App\Model\Release;
use App\Traits\ManagesDocker;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * Class StopCommand
 */
class StopCommand extends Command implements ShouldBeQueued, SelfHandling
{
    use ManagesDocker;

    /**
     * @var Release
     */
    private $release;

    /**
     * @param Release $release
     */
    function __construct(Release $release)
    {
        $this->release = $release;
    }

    /**
     * Executes command
     */
    public function handle()
    {
        $this->release->status(Release::STATUS_STOPPING);
        $this->release->logger()->info(sprintf("Stopping release %s", $this->release->name()));
        foreach ($this->release->containers() as $c) {
            $this->release->logger()->info(sprintf("Stopping container %s - %s", $c->getName(), $c->getId()));
            $this->containers()->stop($c);
        }
        $this->release->status(Release::STATUS_STOPPED);
        $this->release->logger()->info("Release stopped");
    }
}
