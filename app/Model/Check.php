<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Check
 *
 * @property int $id
 * @property string $type
 * @property string $container
 * @property array $params
 * @property Repo $repo
*/
class Check extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['type', 'params', 'container', 'repo_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }

    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = json_encode($value);
    }

    public function getParamsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param string $name
     * @return null
     */
    public function getParam($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }

        return null;
    }
}
