<?php namespace App\Commands\Repo;

use App\Commands\Command;
use App\Events\Event;
use App\Model\Release;
use App\Traits\ManagesDocker;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class ComposeCommand
 */
class ComposeCommand extends Command implements ShouldQueue, SelfHandling
{
    use ManagesDocker;

    const CONFIG = "dtool.yml";

    /**
     * @var Release
     */
    protected $release;

    /**
     * @var string
     */
    private $cmd;

    /**
     * @var Event
     */
    private $event;

    /**
     * @param Release $release
     * @param string|array $cmd
     * @param Event $event
     */
    function __construct(Release $release, $cmd, Event $event = null)
    {
        $this->release = $release;
        $this->cmd = (array) $cmd;
        $this->event = $event;
    }

    /**
     * Run docker-compose command
     */
    public function handle()
    {
        $this->compose()->run($this->release, $this->cmd);
        if ($this->event) {
            app('events')->fire($this->event);
        }
    }

    public function run()
    {
        $this->handle();
    }
}
