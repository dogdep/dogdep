<?php namespace App\CheckHandlers;

use App\Model\Check;
use App\Model\Release;

/**
 * Interface CheckHandlerInterface
 */
interface CheckHandlerInterface
{
    /**
     * @param Check $check
     * @return bool
     */
    public function supports(Check $check);

    /**
     * @param Check $check
     * @param Release $release
     * @return void
     * @throws \App\Exceptions\CheckFailedException
     */
    public function check(Check $check, Release $release);
}
