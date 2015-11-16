<?php namespace App\Model;

use App\Traits\UsesFilesystem;
use Gitonomy\Git\Repository;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\Finder;

/**
 * Class Repo
 *
 * @property int $id
 * @property string $url
 * @property string $name
 * @property string $compose_yml
 * @property string $group
 * @property string $domain
 * @property array $env
 * @property Command[] $commands
 * @property Volume[] $volumes
 * @property Check[] $checks
 */
class Repo extends Model
{
    use UsesFilesystem;

    /**
     * @var array
     */
    protected $fillable = ['url', 'name', 'compose_yml', 'group', 'domain', 'env'];

    /**
     * @var Repository
     */
    private $repo = null;

    /**
     * @return Repository
     */
    public function get()
    {
        if (is_dir($this->path()) && is_null($this->repo)) {
            $this->repo = new Repository($this->path(), config('git.options'));
        }

        return $this->repo;
    }

    /**
     * @return string
     */
    public function path()
    {
        return config('git.path') . DIRECTORY_SEPARATOR . $this->name;
    }

    /**
     * @param string $idOrName
     * @return Release|null
     */
    public function release($idOrName)
    {
        foreach ($this->releases() as $release) {
            if ($release->id() == $idOrName || $release->name() == $idOrName) {
                return $release;
            }
        }

        return null;
    }

    /**
     * @return Release[]
     */
    public function releases()
    {
        $releases = [];

        if (!is_dir($this->releasesPath())) {
            return [];
        }

        foreach (Finder::create()->directories()->depth(0)->in($this->releasesPath()) as $releasePath) {
            $releases[] = new Release($this, basename($releasePath));
        }

        return $releases;
    }

    /**
     * @return string
     */
    private function releasesPath()
    {
        return storage_path(sprintf('releases/%s/%s', $this->group, $this->name));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function delete()
    {
        $this->fs()->deleteDirectory($this->path());
        $this->fs()->deleteDirectory($this->releasesPath());
        parent::delete();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checks()
    {
        return $this->hasMany('App\Model\Check');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commands()
    {
        return $this->hasMany('App\Model\Command')->orderBy('order', 'asc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function volumes()
    {
        return $this->hasMany('App\Model\Volume');
    }

    /**
     * @return string
     */
    public function domain()
    {
        return empty($this->domain) ? $this->name : $this->domain;
    }

    /**
     * @return array
     */
    public function getBranches()
    {
        $branches = [];

        if (!$this->get()) {
            return [];
        }

        /** @var \Gitonomy\Git\Reference\Branch $branch */
        foreach ($this->get()->getReferences()->getBranches() as $branch) {
            $branches[] = $branch->getName();
        }

        return $branches;
    }

    public function toArray()
    {
        $releases = [];

        if (is_dir($this->releasesPath())) {
            foreach (Finder::create()->directories()->depth(0)->in($this->releasesPath()) as $releasePath) {
                $releases[] = basename($releasePath);
            }
        }

        return [
            'env' => (object) $this->env,
        ] + parent::toArray() + [
            'branches' => $this->getBranches(),
            'commands' => $this->commands,
            'volumes' => $this->volumes,
            'checks' => $this->checks,
            'releases' => $releases,
        ];
    }

    public function setEnvAttribute($value)
    {
        $this->attributes['env']  = is_array($value) ? json_encode($value) : null;
    }

    public function getEnvAttribute($value)
    {
        return (array) json_decode($value, true);
    }
}
