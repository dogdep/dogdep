<?php namespace App\Http\Controllers;

use App\Http\Requests\Repo\CreateCheck;
use App\Http\Requests\Request;
use App\Model\Check;

/**
 * Class CheckController
 */
class CheckController extends Controller
{
    /**
     * @param Check $check
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Check $check)
    {
        $check->delete();

        return $check;
    }

    /**
     * @param CreateCheck $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(CreateCheck $request)
    {
        return Check::create([
            'repo_id' => $request->get('repo_id'),
            'order' => $request->get('order'),
            'type'=>$request->get('type'),
            'command'=>$request->get('command'),
            'params'=>$request->get('params'),
            'container'=>$request->get('container'),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index(Request $request)
    {
        $checks = Check::query();
        if ($request->has('repo_id')) {
            $checks->where("repo_id", $request->get('repo_id'));
        }

        return $checks->get();
    }
}
