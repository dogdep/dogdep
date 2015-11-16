<?php namespace App\Providers;

use App\Services\GitlabProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Socialite::extend('gitlab', function() {
            return \Socialite::buildProvider(GitlabProvider::class, config('services.gitlab'));
        });
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'App\Services\Registrar'
        );

        $this->app->bind('Pusher', function() {
            return new \Pusher(
                config('services.pusher.key'),
                config('services.pusher.secret'),
                config('services.pusher.id'),
                ['encrypted' => true]
            );
        });

        $this->app->alias("Pusher", "pusher");
    }

}
