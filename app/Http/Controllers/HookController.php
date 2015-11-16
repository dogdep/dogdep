<?php namespace App\Http\Controllers;

use App\Commands\Repo\UpdateJob;
use App\Model\Repo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
* Class HookController
*/
class HookController extends Controller
{
    /**
    * @param Request $request
    * @return \Illuminate\Database\Eloquent\Collection|static[]
    */
    public function gitlab(Request $request)
    {
        if ($request->get('object_kind') == "push") {
            return $this->servePushRequest($request);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    private function servePushRequest(Request $request)
    {
        $repoUrl = $request->get('repository')['url'];
        $repo = Repo::where('url', $repoUrl)->first();

        if ($repo) {
            $this->dispatch(new UpdateJob($repo));
        }

        return new JsonResponse();
    }
}
