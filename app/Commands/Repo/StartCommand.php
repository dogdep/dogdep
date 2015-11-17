<?php namespace App\Commands\Repo;

use App\Events\ReleaseEvent;
use App\Model\Release;

/**
 * Class StartCommand
 */
class StartCommand extends ComposeCommand
{
    /**
     * @param Release $release
     */
    function __construct(Release $release)
    {
        parent::__construct($release, ["up", "-d"], ReleaseEvent::postStart($release));
    }

    public function handle()
    {
        try {
            $this->release->status(Release::STATUS_STARTING);
            app('events')->fire(new ReleaseEvent($this->release, ReleaseEvent::PRE_START));
            parent::handle();
            app('events')->fire(new ReleaseEvent($this->release, ReleaseEvent::POST_START));
            $this->release->status(Release::STATUS_STARTED);
        } catch (\Exception $e) {
            $this->release->status(Release::STATUS_ERROR);
            $this->release->logger()->error("Error while starting release: " . $e->getMessage());
            throw $e;
        }
    }
}
