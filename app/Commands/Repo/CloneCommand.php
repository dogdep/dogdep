<?php namespace App\Commands\Repo;

use App\Commands\Command;
use App\Model\Repo;
use Gitonomy\Git\Admin;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * Class PullCommand
 */
class CloneCommand extends Command implements ShouldBeQueued, SelfHandling
{
    /**
     * @var Repo
     */
    private $repo;

    /**
     * @param Repo $repo
     */
    function __construct(Repo $repo)
    {
        $this->repo = $repo;
    }

    public function handle()
    {
        if (!$this->repo->get()) {
            Admin::cloneTo($this->repo->path(), $this->repo->url, true, config('git.options'));
        }
        $this->repo->get()->run('config', ['remote.origin.fetch', 'refs/heads/*:refs/heads/*']);
    }
}
