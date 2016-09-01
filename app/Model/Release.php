<?php namespace App\Model;

use App\Docker\Exception\APIException;
use App\Services\Pusher\ReleasePusher;
use App\Traits\UsesFilesystem;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Release
 */
class Release implements Arrayable, JsonSerializable, Jsonable
{
    use UsesFilesystem;

    const STATUS_UNKNOWN = "UNKNOWN";
    const STATUS_INITIATING = "INITIATING";
    const STATUS_STARTING_QUEUED = "STARTING_QUEUED";
    const STATUS_STARTING = "STARTING";
    const STATUS_STARTED = "STARTED";
    const STATUS_STOPPING = "STOPPING";
    const STATUS_STOPPING_QUEUED = "STOPPING_QUEUED";
    const STATUS_STOPPED = "STOPPED";
    const STATUS_DESTROYED = "DESTROYED";
    const STATUS_DESTROYING = "DESTROYING";
    const STATUS_DESTROYING_QUEUED = "DESTROYING_QUEUED";
    const STATUS_ERROR = "ERROR";
    const STATUS_BUILDING = "BUILDING";

    const LOG_FORMAT = "[%level_name%] %message%\n";

    /**
     * @var Logger
     */
    private static $log = [];

    /**
     * @var Repo
     */
    private $repo;

    /**
     * @var string
     */
    private $id;

    /**
     * @var \App\Docker\Container[]
     */
    private $containers = null;

    /**
     * @var string
     */
    protected $presenter = 'App\Model\Presenter\ReleasePresenter';

    /**
     * @param Repo $repo
     * @param string $id
     */
    function __construct(Repo $repo, $id)
    {
        $this->repo = $repo;
        $this->id = $id;
    }

    /**
     * @return Command[]
     */
    public function commands()
    {
        return $this->repo()->commands;
    }

    /**
     * @param null $service
     * @return string
     */
    public function domain($service = null)
    {
        $baseDomain = $this->id() . '.' . config('app.domain');

        if ($service) {
            $baseDomain = "$service.$baseDomain";
        }

        return $this->repo()->domain() . '.' . $baseDomain;
    }

    /**
     * @return Volume[]
     */
    public function volumes()
    {
        return $this->repo()->volumes;
    }

    /**
     * @param null $nameOrId
     * @return \App\Docker\Container|\App\Docker\Container[]
     * @throws \App\Docker\Exception\ContainerNotFoundException
     * @throws \App\Docker\Exception\UnexpectedStatusCodeException
     */
    public function containers($nameOrId = null)
    {
        /** @var \App\Docker\Docker $docker */
        $docker = app('docker');

        $manager = $docker->getContainerManager();

        $containers = [];

        if (is_null($this->containers)) {
            $this->containers = $manager->findAll(['all' => true]);
        }

        foreach ($this->containers as $container) {
            try {
                if (stripos(ltrim($container->getName(), '/'), $this->name()) === 0) {
                    $containers[] = $container;

                    $manager->inspect($container);
                    if (!is_null($nameOrId)) {
                        if ($container->getId() == $nameOrId || stripos($container->getName(), "/" . $this->name() . "_" . $nameOrId) === 0) {
                            return $container;
                        }
                    }
                }
            } catch (APIException $e) {
                // ignore
            }
        }

        return is_null($nameOrId) ? $containers : null;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    public function init($commit = null)
    {
        if (!$this->fs()->isDirectory($this->path())) {
            $this->fs()->makeDirectory($this->path(), 0777, true, true);

            if ($commit) {
                $this->fs()->put($this->path("RELEASE.rev"), $commit);
            }
        }

        $this->status(self::STATUS_INITIATING);
    }

    /**
     * @return \Gitonomy\Git\Commit|null
     */
    public function commit()
    {
        $revFile = $this->path('RELEASE.rev');
        if (!file_exists($revFile) || !is_readable($revFile)) {
            return null;
        }
        $rev = file_get_contents($revFile);

        return $this->repo()->get()->getCommit($rev);
    }

    public function log()
    {
        if (is_file($this->path('release.log'))) {
            return file_get_contents($this->path('release.log'));
        }

        return false;
    }

    public function status($set = null)
    {
        if (!is_null($set)) {
            if ($set != self::STATUS_DESTROYED) {
                @file_put_contents($this->path("RELEASE.status"), $set);
                @chmod($this->path("RELEASE.status"), 0777);
            }

            $this->pusher()->push(['status'=>$set]);
        }

        $status = @file_get_contents($this->path('RELEASE.status'));

        return !empty($status) ? $status : self::STATUS_UNKNOWN;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->repo->name . $this->id;
    }

    /**
     * @param string|null $dir
     * @return string
     */
    public function path($dir = null)
    {
        $base = $this->rootPath("current");
        return is_null($dir) ? $base : $base . '/' . ltrim($dir, '/\\');
    }

    /**
     * @param string|null $dir
     * @return string
     */
    public function rootPath($dir = null)
    {
        $base = sprintf("%s/%s/%s/%s", storage_path('releases'), $this->repo->group, $this->repo->name, $this->id);

        return is_null($dir) ? $base : $base . '/' . ltrim($dir, '/\\');
    }

    /**
     * @return string
     */
    public function remotePath()
    {
        return sprintf('%s/%s/%s/%s', config('docker.releases_dir'), $this->repo->group, $this->repo->name, $this->id);
    }

    public function repo()
    {
        return $this->repo;
    }

    /**
     * @return string
     */
    public function containerStatus()
    {
        $containers = $this->containers();
        $up = 0;

        foreach ($containers as $container) {
            $data = $container->getData();

            if (strpos($data['Status'], "Up") === 0) {
                $up++;
            }
        }

        if ($up == count($containers) && count($containers) > 0) {
            return "Up";
        }

        return $up == 0 ? "Down" : $up . "/" . count($containers) . " Up";
    }

    public function date()
    {
        return \DateTime::createFromFormat(DATE_ISO8601, date(DATE_ISO8601, filectime($this->path())));
    }

    /**
     * @return Logger
     */
    public function logger()
    {
        if (!isset(self::$log[$this->name()])) {
            $file = new StreamHandler($this->path('release.log'));
            $file->setFormatter(new LineFormatter(self::LOG_FORMAT, null, false, true));

            self::$log[$this->name()] = new Logger("repo-{$this->repo->id}", [$file, $this->pusher()]);
        }

        return self::$log[$this->name()];
    }

    public function pusher()
    {
        return ReleasePusher::get($this);
    }

    public function ymlPath()
    {
        $yml = $this->repo->compose_yml;

        if (!empty($yml) && file_exists($file = $this->path($yml))) {
            return $file;
        }

        $defaultFiles = [
            "docker-compose.yml",
            "docker-compose.yml.dist",
            "docker-compose.yml.sample",
            "docker-compose.yml.example"
        ];

        foreach ($defaultFiles as $expectedFile) {
            if (file_exists($file = $this->path($expectedFile))) {
                return $file;
            }
        }

        throw new \RuntimeException('Can not find docker-compose yml!');
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $commit = $this->commit();

        return [
            'id' => $this->id(),
            'status' => $this->status(),
            'date' => $this->date()->format(DATE_ISO8601),
            'containerStatus' => $this->containerStatus(),
            'containers' => $this->containers(),
            'repo' => $this->repo(),
            'log' => file_exists($this->path('release.log')) ? file($this->path('release.log')) : [],
            'commit' => !$commit ? null : [
                'hash' => $commit->getHash(),
                'short_hash' => $commit->getShortHash(),
                'author_email' => $commit->getAuthorEmail(),
                'author_name' => $commit->getAuthorName(),
                'message' => $commit->getShortMessage(),
                'date' => $commit->getAuthorDate()->format(DATE_ISO8601),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
