<?php namespace App\Events;

use App\Exceptions\CheckFailedException;
use App\Model\Check;
use App\Model\Release;

/**
 * Class CheckEvent
 */
class CheckEvent extends Event
{
    /**
     * @var Check
     */
    private $check;

    /**
     * @var CheckFailedException
     */
    private $failure;

    /**
     * @var Release
     */
    private $release;

    /**
     * @param Check $check
     * @param Release $release
     * @param CheckFailedException $failure
     */
    function __construct(Check $check, Release $release, CheckFailedException $failure = null)
    {
        $this->check = $check;
        $this->failure = $failure;
        $this->release = $release;
    }

    /**
     * @return bool
     */
    public function checkSucceeded()
    {
        return is_null($this->failure);
    }

    /**
     * @return Check
     */
    public function getCheck()
    {
        return $this->check;
    }

    /**
     * @return CheckFailedException
     */
    public function getException()
    {
        return $this->failure;
    }

    /**
     * @return Release
     */
    public function getRelease()
    {
        return $this->release;
    }
}
