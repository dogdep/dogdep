<?php namespace App\Http\Controllers;

use App\Commands\Repo\DeployCommand;
use App\Commands\Repo\DestroyCommand;
use App\Commands\Repo\ExecCommand;
use App\Commands\Repo\RunChecksCommand;
use App\Commands\Repo\StartCommand;
use App\Commands\Repo\StopCommand;
use App\Model\Command;
use App\Model\Release;
use App\Model\Repo;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

/**
 * Class ReleaseController
 */
class ReleaseController extends Controller
{
    use DispatchesJobs;

    /**
     * @param Repo $repo
     * @param $releaseId
     * @return Release|null
     */
    public function check(Repo $repo, $releaseId)
    {
        $release = $this->release($repo, $releaseId);

        foreach ($release->repo()->checks as $check) {
            $this->dispatch(new RunChecksCommand($release, $check));
        }

        return $release;
    }

    /**
     * @param Repo $repo
     * @param string $commit
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Repo $repo, $commit, Request $request)
    {
        $this->dispatch(new DeployCommand($repo, $commit, $request->get('release_id')));

        return $repo;
    }

    public function config(Repo $repo, $releaseId)
    {
        $release = $this->release($repo, $releaseId);

        if (!$release) {
            abort(404, 'Release not found');
        }

        return file_get_contents($release->path('dtool.yml'));
    }

    /**
     * @param Repo $repo
     * @param string $releaseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop(Repo $repo, $releaseId)
    {
        $release = $this->release($repo, $releaseId);

        $this->dispatch(new StopCommand($release));
        $release->status(Release::STATUS_STOPPING_QUEUED);

        return $release;
    }

    /**
     * @param Repo $repo
     * @param string $releaseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(Repo $repo, $releaseId)
    {
        $release = $this->release($repo, $releaseId);

        $this->dispatch(new StartCommand($release));
        $release->status(Release::STATUS_STARTING_QUEUED);

        return $release;
    }

    /**
     * @param Repo $repo
     * @param string $releaseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Repo $repo, $releaseId)
    {
        $release = $this->release($repo, $releaseId);

        $this->dispatch(new DestroyCommand($release));
        $release->status(Release::STATUS_DESTROYING_QUEUED);

        return $release;
    }

    /**
     * @param Repo $repo
     * @param string $releaseId
     * @return \Illuminate\View\View
     */
    public function log(Repo $repo, $releaseId)
    {
        $release = $this->release($repo, $releaseId);

        return ["log"=>$release->log()];
    }

    /**
     * @param Repo $repo
     * @param string $releaseId
     * @return \App\Model\Release|null
     */
    public function release(Repo $repo, $releaseId)
    {
        $release = $repo->release($releaseId);
        if (!$release) {
            abort(404, 'Release not found');
        }

        return $release;
    }

    public function run(Repo $repo, $releaseId, Command $command)
    {
        $release = $repo->release($releaseId);
        if (!$release) {
            abort(404, 'Release not found');
        }

        $this->dispatch(new ExecCommand($command, $release));

        return $release;
    }
}
