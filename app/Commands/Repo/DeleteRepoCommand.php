<?php namespace App\Commands\Repo;

use App\Commands\Command;
use App\Model\Repo;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class DeleteRepoCommand
 */
class DeleteRepoCommand extends Command implements ShouldQueue, SelfHandling
{
    /**
     * @param Repo $repo
     * @param string $revision
     */
    function __construct(Repo $repo, $revision = 'master')
    {
        $this->repo = $repo;
    }

    /**
     * Delete repo
     */
    public function handle()
    {
        $this->repo->delete();
    }
}
