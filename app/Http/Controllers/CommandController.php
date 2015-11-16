<?php namespace App\Http\Controllers;

use App\Http\Requests\Repo\CreateCommand;
use App\Model\Command;
use Illuminate\Http\Request;

/**
 * Class CommandController
 */
class CommandController extends Controller
{
    /**
     * @param Command $command
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Command $command)
    {
        $command->delete();

        return $command;
    }


    /**
     * @param CreateCommand $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(CreateCommand $request)
    {
        return Command::create([
            'repo_id' => $request->get('repo_id'),
            'order' => $request->get('order'),
            'type'=>$request->get('type'),
            'command'=>$request->get('command'),
            'container'=>$request->get('container'),
        ]);
    }

    /**
     * @param Command $command
     * @param Request $request
     * @return Command
     */
    public function update(Command $command, Request $request)
    {
        $command->order = (int) $request->get('order');
        $command->save();
        return $command;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index(Request $request)
    {
        $commands = Command::query();
        if ($request->has('repo_id')) {
            $commands->where("repo_id", $request->get('repo_id'));
        }

        return $commands->orderBy('order')->get();
    }
}
