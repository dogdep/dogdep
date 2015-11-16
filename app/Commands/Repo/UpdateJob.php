<?php namespace App\Commands\Repo;


use App\Commands\Command;
use App\Model\Repo;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Pusher;

/**
 * Class UpdateJob
 */
class UpdateJob extends Command implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;

    /**
     * @var Repo
     */
    private $repo;

    /**
     * @param Repo $repo
     */
    public function __construct(Repo $repo)
    {
        $this->repo = $repo;
    }

    public function handle(Pusher $pusher)
    {
        if ($this->repo->get()) {
            $this->repo->get()->run('fetch', ['--all', '--prune']);
            $pusher->trigger(['pulls'], 'repo-' . $this->repo->id, []);
        } else {
            $this->dispatch(new CloneCommand($this->repo));
        }
    }
}
