<?php namespace App\Http\Controllers;

use App\Docker\Container;
use App\Traits\ManagesDocker;

/**
 * Class DockerController
 */
class DockerController extends Controller
{
    use ManagesDocker;

    /**
     * @param Container $container
     * @return \Illuminate\View\View
     */
    public function log(Container $container)
    {
        return $this->containerLog($container->getId());
    }

    /**
     * @param Container $container
     * @return \Illuminate\Http\RedirectResponse
     * @throws \App\Docker\Exception\UnexpectedStatusCodeException
     */
    public function start(Container $container)
    {
        $this->containers()->start($container);

        return redirect()->back();
    }

    /**
     * @param Container $container
     * @return \Illuminate\Http\RedirectResponse
     * @throws \App\Docker\Exception\UnexpectedStatusCodeException
     */
    public function stop(Container $container)
    {
        $this->containers()->stop($container);

        return redirect()->back();
    }

    /**
     * @param Container $container
     * @return \Illuminate\Http\RedirectResponse
     * @throws \App\Docker\Exception\UnexpectedStatusCodeException
     */
    public function restart(Container $container)
    {
        $this->containers()->restart($container);

        return $container;
    }

    /**
     * @param Container $container
     * @return array
     */
    public function inspect(Container $container)
    {
        return $container->getRuntimeInformations();
    }

    /**
     * @param Container $container
     * @return \Illuminate\Http\RedirectResponse
     * @throws \App\Docker\Exception\UnexpectedStatusCodeException
     */
    public function remove(Container $container)
    {
        $this->containers()->remove($container, true);

        return redirect()->back();
    }


    /**
     * @param Container $container
     * @return \Illuminate\Http\RedirectResponse
     * @throws \App\Docker\Exception\UnexpectedStatusCodeException
     */
    public function get(Container $container)
    {
        return $container;
    }

    /**
     * @param Container $container
     * @return array
     * @throws \App\Docker\Exception\UnexpectedStatusCodeException
     */
    public function top(Container $container)
    {
        return $this->containers()->top($container);
    }
}
