<?php namespace App\Services\Pusher;

use App\Model\Release;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

/**
 * Class ReleasePusher
 */
class ReleasePusher extends AbstractHandler {
    /**
     * @var ReleasePusher[]
     */
    private static $instances = [];

    /**
     * @var Release
     */
    private $release;

    public function __construct(Release $release)
    {
        parent::__construct(Logger::DEBUG, false);
        $this->release = $release;
    }

    /**
     * @param array $record
     * @return bool|void
     */
    public function handle(array $record)
    {
        $this->push(['message' => sprintf("[%s] %s", $record['level_name'], $record['message'])]);
    }

    /**
     * @param Release $release
     * @return ReleasePusher
     */
    public static function get(Release $release)
    {
        if (!isset(self::$instances[$release->id()])) {
            self::$instances[$release->id()] = new static($release, app('pusher'));
        }

        return self::$instances[$release->id()];
    }

    /**
     * @param $msg
     * @return mixed
     */
    public function push(array $msg)
    {
        return app('pusher')->trigger(['releases'], "repo-{$this->release->repo()->id}", $msg + [
            'id' => $this->release->id(),
            'repo' => $this->release->repo()->id,
        ]);
    }
}
