<?php namespace App\Http\Controllers;

use App\Git\SSH;
use App\Traits\ManageFilesystem;
use App\Traits\UsesFilesystem;

/**
 * Class ConfigController
 */
class ConfigController extends Controller
{
    use UsesFilesystem;

    public function get()
    {
        $publicKeyPath = config('git.public_key');
        if (!is_file($publicKeyPath)) {
            SSH::writeKeyPair('dogdep', config('git.public_key'), config('git.private_key'));
        }
        $public = file_get_contents($publicKeyPath);

        return ['public_key' => $public];
    }
}
