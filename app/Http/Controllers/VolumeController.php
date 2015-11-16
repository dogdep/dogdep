<?php namespace App\Http\Controllers;

use App\Http\Requests\Repo\CreateVolume;
use App\Model\Volume;
use Illuminate\Http\Request;

/**
 * Class VolumeController
 */
class VolumeController extends Controller
{
    /**
     * @param Volume $volume
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Volume $volume)
    {
        $volume->delete();

        return $volume;
    }

    /**
     * @param CreateVolume $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(CreateVolume $request)
    {
        return Volume::create([
            'repo_id' => $request->get('repo_id'),
            'volume' => $request->get('volume'),
            'container' => $request->get('container'),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index(Request $request)
    {
        $commands = Volume::query();
        if ($request->has('repo_id')) {
            $commands->where("repo_id", $request->get('repo_id'));
        }

        return $commands->get();
    }
}
