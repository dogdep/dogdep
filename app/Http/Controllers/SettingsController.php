<?php namespace App\Http\Controllers;

use App\Commands\Repo\ExecCommand;
use App\Http\Requests\Repo\CreateCommand;
use App\Model\Command;
use App\Model\Repo;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Http\Request;

/**
 * Class SettingsController
 */
class SettingsController extends Controller
{
    use DispatchesCommands;


    /**
     * @param Command $command
     * @param string $release
     * @return \Illuminate\Http\RedirectResponse
     */
    public function run(Command $command, $release)
    {
        $this->dispatch(new ExecCommand($command, $command->repo->release($release)));
        return redirect()->back();
    }
}
