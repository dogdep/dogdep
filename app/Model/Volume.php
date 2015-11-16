<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Volume
 *
 * @property int $id
 * @property int $repo_id
 * @property string $container
 * @property string $volume
 * @property Repo $repo
 */
class Volume extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['volume', 'container', 'repo_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repo()
    {
        return $this->belongsTo('App\Model\Repo');
    }

    /**
     * @param string $mountPath
     * @return array
     */
    public function getMountPath($mountPath)
    {
        $mountPaths = $this->getMountPaths();

        if (isset($mountPaths[$this->volume])) {
            return $mountPaths[$this->volume];
        }

        return ["$mountPath/volumes/" . ltrim($this->volume, "\\/"), $this->volume];
    }

    private function getMountPaths()
    {
        return [
            'composer' => ['/tmp/.composer', '/root/.composer'],
            'npm' => ['/tmp/.npm', '/root/.npm'],
            'bower' => ['/tmp/.bower', '/root/.bower'],
        ];
    }
}
