<?php namespace App\Http\Controllers;

use App\Commands\Repo\CloneCommand;
use App\Commands\Repo\DeleteRepoCommand;
use App\Commands\Repo\DestroyCommand;
use App\Commands\Repo\UpdateJob;
use App\Http\Requests\Repo\CreateRepo;
use App\Http\Requests\Repo\UpdateRepo;
use App\Model\Repo;
use App\Traits\ManagesDocker;
use App\Traits\UsesFilesystem;
use Gitonomy\Git\Commit;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

/**
 * Class RepoController
 */
class RepoController extends Controller
{
    use ManagesDocker;
    use DispatchesJobs;
    use UsesFilesystem;

    /**
     * @param Repo $repo
     * @param UpdateRepo $request
     * @return \Illuminate\Http\Response
     */
    public function update(Repo $repo, UpdateRepo $request)
    {
        $repo->compose_yml = $request->get('compose_yml');
        $repo->domain = $request->get('domain');
        $repo->env = $request->get('env');
        $repo->save();

        return new JsonResponse($repo);
    }

    /**
     * @param CreateRepo $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRepo $request)
    {
        $repo = Repo::create([
            'url' => $request->get('url'),
            'name' => strtolower($request->get('name')),
            'group' => strtolower($request->get('group')),
        ]);

        $this->dispatch(new CloneCommand($repo));

        return new JsonResponse($repo);
    }

    /**
     * @return Repo[]
     */
    public function repoList()
    {
        return Repo::all();
    }

    /**
     * @param Repo $repo
     * @return Repo
     */
    public function repo(Repo $repo)
    {
        return $repo->toArray();
    }

    /**
     * @param Repo $repo
     * @return array
     */
    public function repoReleases(Repo $repo)
    {
        $releases = [];

        foreach ($repo->releases() as $rel) {
            $releases[] = $rel->toArray();
        }

        return $releases;
    }

    /**
     * @param Repo $repo
     * @return \App\Model\Command[]
     */
    public function commands(Repo $repo)
    {
        return $repo->commands;
    }

    /**
     * @param Repo $repo
     * @param Request $request
     * @return \Gitonomy\Git\Log
     */
    public function commits(Repo $repo, Request $request)
    {
        $page = max(Input::get('page', 1), 1) - 1;

        if (!$repo->get()) {
            return [];
        }

        try {
            $branch = urldecode($request->get('branch'));

            if (!$repo->get()->getReferences()->hasBranch($branch)) {
                $branch = null;
            }

            $commits = $repo->get()->getLog($branch)->setLimit(10)->setOffset($page * 10);
        } catch (\Exception $e) {
            $commits = [];
        }

        $response = [];

        /** @var Commit $commit */
        foreach ($commits as $commit) {
            $response[] = [
                'hash' => $commit->getHash(),
                'name' => $commit->getAuthorName(),
                'shortHash' => $commit->getShortHash(),
                'shortMessage' => $commit->getShortMessage(),
                'email' => $commit->getAuthorEmail(),
                'image' => "//www.gravatar.com/avatar/" . md5($commit->getAuthorEmail()),
                'date' => $commit->getAuthorDate()->format(DATE_ISO8601),
            ];
        }

        return $response;
    }

    /**
     * @param Repo $repo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Repo $repo)
    {
        foreach ($repo->releases() as $release) {
            $this->dispatch(new DestroyCommand($release));
        }

        $this->dispatch(new DeleteRepoCommand($repo));

        return new JsonResponse();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function repoDropdown()
    {
        return view('repo.partial_list', ['repos'=>Repo::all()]);
    }

    /**
     * Update repo commit log from GitLab
     *
     * @param Repo $repo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pull($repo)
    {
        $this->dispatch(new UpdateJob($repo));

        return new JsonResponse();
    }
}
