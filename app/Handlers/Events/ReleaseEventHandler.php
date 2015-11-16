<?php namespace App\Handlers\Events;

use App\Events\ReleaseEvent;

use App\Model\Release;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class ReleaseEventHandler implements ShouldBeQueued
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param ReleaseEvent $event
     */
    public function handle(ReleaseEvent $event)
    {
        $release = $event->release();
        $log = $release->logger();

        $log->info(sprintf("Executing %s handlers for %s", $event->type(), $release->name()));
        $this->runCommands($event, $release);
        $log->info(sprintf("Done executing %s handlers for %s", $event->type(), $release->name()));
    }

    /**
     * @param ReleaseEvent $event
     * @param $release
     */
    private function runCommands(ReleaseEvent $event, Release $release)
    {
        foreach ($event->commands() as $command) {
            $release->logger()->info(
                sprintf("Running command #%s (%s) in %s", $command->id, $command->command, $command->container)
            );
            $command->get($release)->run();
        }
    }
}
