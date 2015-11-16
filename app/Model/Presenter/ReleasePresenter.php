<?php namespace App\Model\Presenter;

use App\Model\Release;
use App\Presentation\Presenter;

/**
 * Class ReleasePresenter
 *
 * @property Release $entity
 */
class ReleasePresenter extends Presenter
{
    /**
     * @var array
     */
    private $statusMap = [
        Release::STATUS_UNKNOWN => "danger",
        Release::STATUS_INITIATING => "info",
        Release::STATUS_STARTING => "info",
        Release::STATUS_STARTING_QUEUED => "primary",
        Release::STATUS_STARTED => "success",
        Release::STATUS_STOPPING => "warning",
        Release::STATUS_STOPPING_QUEUED => "warning",
        Release::STATUS_STOPPED => "warning",
        Release::STATUS_DESTROYING => "danger",
        Release::STATUS_DESTROYING_QUEUED => "danger",
        Release::STATUS_BUILDING => "info",
        Release::STATUS_ERROR => "danger",
    ];

    /**
     * @return mixed
     */
    public function statusClass()
    {
        return $this->statusMap[$this->entity->status()];
    }

    public function containerStatusClass()
    {
        $status = $this->entity->containerStatus();

        switch($status) {
            case "Up": return "success";
            case "Down": return "danger";
            default: return "warning";
        }
    }

    public function status()
    {
        return ucwords(str_replace('_', ' ', $this->entity->status()));
    }
}
