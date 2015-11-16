<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class CheckHandlerProvider
 */
class CheckHandlerProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->tag([
            'App\CheckHandlers\HttpCheckHandler',
            'App\CheckHandlers\CommandCheckHandler',
        ], 'check_handler');

        $this->app->tag([
            'App\CheckNotifiers\SlackNotifier'
        ], 'check_notifier');
    }
}
