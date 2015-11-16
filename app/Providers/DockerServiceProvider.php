<?php namespace App\Providers;

use App\Services\DockerCompose;
use App\Docker\Docker;
use App\Docker\Http\DockerClient;
use Illuminate\Support\ServiceProvider;

/**
 * Class DockerServiceProvider
 */
class DockerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('docker', function(){
            return new Docker(new DockerClient([], config('docker.socket')));
        });

        $this->app->singleton('docker-compose', function(){
            return new DockerCompose(config('docker.socket'));
        });
    }
}
