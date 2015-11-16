<?php namespace App\Commands\Repo;

use App\Commands\Command;
use App\Events\ReleaseEvent;
use App\Model\Release;
use App\Traits\ManagesDocker;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * Class UpCommand
 */
class UpCommand extends Command implements ShouldBeQueued, SelfHandling
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
     *  Build and Up
     */
    public function handle()
    {
        try {
            $this->release->status(Release::STATUS_BUILDING);
            $this->compose()->run($this->release, ["build"]);
            $this->release->status(Release::STATUS_STARTING);
            $this->compose()->run($this->release, ["up", "-d"]);
            $this->release->status(Release::STATUS_STARTED);
        } catch (\Exception $e) {
            $this->release->logger()->error("Error while running composer up: " . $e->getMessage());
            $this->release->status(Release::STATUS_ERROR);
        }

        app('events')->fire(new ReleaseEvent($this->release, ReleaseEvent::POST_RELEASE));

        foreach ($this->release->repo()->checks as $check) {
            $this->dispatch(new RunChecksCommand($this->release, $check));
        }
    }
}
