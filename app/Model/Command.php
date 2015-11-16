<?php namespace App\Model;

use App\Commands\Repo\ComposeCommand;
use App\Traits\ManagesDocker;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Command
 *
 * @property int $id
 * @property int $repo_id
 * @property string $type
 * @property string $container
 * @property string $command
 * @property int $order
 * @property Repo $repo
 */
class Command extends Model
{
    use ManagesDocker;

    /**
     * @var array
     */
    protected $fillable = ['command', 'type', 'container', 'repo_id', 'order'];

    /**
     * @param Release $release
     * @return ComposeCommand
     */
    public function get(Release $release)
    {
        $command = array_merge(['run', '-T', /*'--rm', */$this->container], explode(' ', $this->command));
        return new ComposeCommand($release, $command);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }
}
