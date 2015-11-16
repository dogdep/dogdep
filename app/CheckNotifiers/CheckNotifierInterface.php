<?php namespace App\CheckNotifiers;

use App\Events\CheckEvent;

/**
 * Interface CheckNotifierInterface

 *
*@package App\CheckNotifiers
 */
interface CheckNotifierInterface
{
    /**
     * @param CheckEvent $event
     */
    public function success(CheckEvent $event);

    /**
     * @param CheckEvent $event
     * @return
     */
    public function failure(CheckEvent $event);
}
