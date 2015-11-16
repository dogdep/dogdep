<?php namespace App\Providers;

use App\Model\SshKey;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider {

    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);

        $router->model('repo', 'App\Model\Repo');
        $router->model('command', 'App\Model\Command');
        $router->model('check', 'App\Model\Check');
        $router->model('volume', 'App\Model\Volume');
        $router->bind('container', function($id) {
            $container = app('docker')->getContainerManager()->find($id);

            if (!$container) {
                abort(404);
            }

            return $container;
        });

        $router->bind('key', function($key) {
            return SshKey::get($key) ?: abort(404);
        });
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function($router)
        {
            require app_path('Http/routes.php');
        });
    }

}
