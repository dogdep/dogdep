<?php namespace App\Handlers\Events;

use App\CheckNotifiers\CheckNotifierInterface;
use App\Events\CheckEvent;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class ChecksHandler
 */
class ChecksHandler implements ShouldBeQueued
{
    use InteractsWithQueue;

    /**
     * @param CheckEvent $event
     * @return bool
     */
    public function handle(CheckEvent $event)
    {
        foreach ($this->getNotifiers() as $notifier) {
            if ($event->checkSucceeded()) {
                $notifier->success($event);
            } else {
                $notifier->failure($event);
            }
        }
    }

    /**
     * @return CheckNotifierInterface[]
     */
    private function getNotifiers()
    {
        return app()->tagged('check_notifier');
    }
}
