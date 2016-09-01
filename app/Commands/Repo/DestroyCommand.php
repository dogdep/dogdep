<?php namespace App\Commands\Repo;

use App\Commands\Command;
use App\Model\Release;
use App\Traits\ManagesDocker;
use App\Traits\UsesFilesystem;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * Class DestroyCommand
 */
class DestroyCommand extends Command implements ShouldBeQueued, SelfHandling
{
    use ManagesDocker;
    use UsesFilesystem;

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

    public function handle()
    {
        $this->release->status(Release::STATUS_DESTROYING);
        try {
            $this->release->logger()->debug(sprintf("Stopping release %s", $this->release->name()));
            $this->compose()->run($this->release, ['stop']);

            $this->release->logger()->debug(sprintf("Remove release %s", $this->release->name()));
            $this->compose()->run($this->release, ['rm', '-v', '-f']);

            if (!$this->fs()->deleteDirectory($this->release->rootPath())) {
                $this->release->logger()->error(sprintf("Failed to remove release dir %s", $this->release->path()));
            }

            $this->release->logger()->info(sprintf("Release destroyed %s", $this->release->name()));
            $this->release->status(Release::STATUS_DESTROYED);
        } catch (\Exception $e) {
            $this->release->status(Release::STATUS_ERROR);
        }
    }
}
