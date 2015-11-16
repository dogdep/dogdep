<?php namespace App\Events;

use App\Model\Release;

/**
 * Class ReleaseEvent
 */
class ReleaseEvent extends Event
{
    const POST_RELEASE = "post-release";
    const PRE_RELEASE = "pre-release";
    const POST_STOP = "post-stop";
    const PRE_STOP = "pre-stop";
    const POST_START = "post-start";
    const PRE_START = "pre-start";

    /**
     * @var Release
     */
    private $release;

    /**
     * @var string
     */
    private $type;

    /**
     * @param Release $release
     * @param string $type
     */
    function __construct(Release $release, $type)
    {
        $this->release = $release;
        $this->type = $type;
    }

    /**
     * @param Release $release
     * @return ReleaseEvent
     */
    public static function postStart(Release $release)
    {
        return new ReleaseEvent($release, self::POST_START);
    }

    /**
     * @return \App\Model\Command[]
     */
    public function commands()
    {
        $commands = [];
        foreach ($this->release->commands() as $command) {
            if ($command->type == $this->type) {
                $commands[] = $command;
            }
        }
        return $commands;
    }

    /**
     * @return Release
     */
    public function release()
    {
        return $this->release;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    public static function postRelease(Release $release)
    {
        return new static($release, self::POST_RELEASE);
    }
}
