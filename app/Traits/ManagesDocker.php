<?php namespace App\Traits;

use App\Docker\Container;
use App\Docker\Stream;

/**
 * Trait ManagesDocker
 */
trait ManagesDocker
{
    /**
     * @return \App\Docker\Docker
     */
    protected function docker()
    {
        return app('docker');
    }

    /**
     * @return \App\Docker\Manager\ContainerManager
     */
    protected function containers()
    {
        return $this->docker()->getContainerManager();
    }

    /**
     * @return \App\Docker\Manager\ImageManager
     */
    protected function images()
    {
        return $this->docker()->getImageManager();
    }

    /**
     * @return \App\Services\DockerCompose
     */
    protected function compose()
    {
        return app('docker-compose');
    }

    /**
     * @param Container|string $container
     * @return string
     */
    protected function containerLog($container)
    {
        $res = $this->docker()->getHttpClient()->get(['/containers/{id}/logs{?data*}', [
            'id' => $container,
            'data' => [
                'follow' => (int)0,
                'stdout' => (int)1,
                'stderr' => (int)1,
                'timestamps' => (int)0,
                'tail' => 'all',
            ],
        ]]);

        return new Stream((string) $res->getBody());
    }
}
