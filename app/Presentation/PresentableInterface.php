<?php namespace App\Presentation;

/**
 * Interface PresentableInterface
 */
interface PresentableInterface
{
    /**
     * Prepare a new or cached presenter instance
     *
     * @return mixed
     */
    public function present();
}
