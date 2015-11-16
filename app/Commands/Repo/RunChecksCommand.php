<?php namespace App\Commands\Repo;

use App\CheckHandlers\CheckHandlerInterface;
use App\Commands\Command;
use App\Events\CheckEvent;
use App\Exceptions\CheckFailedException;
use App\Model\Check;
use App\Model\Release;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Queue\SerializesModels;

/**
 * Class RunChecksCommand
 */
class RunChecksCommand extends Command implements SelfHandling, ShouldBeQueued
{
    use SerializesModels;
    use DispatchesCommands;

    /**
     * @var Release
     */
    private $release;

    /**
     * @var \App\Model\Check
     */
    private $check;

    /**
     * @var int
     */
    private $attempt = 1;

    /**
     * @param Release $release
     * @param Check $check
     */
    function __construct(Release $release, Check $check)
    {
        $this->release = $release;
        $this->check = $check;
    }

    /**
     * @return int
     */
    public function getAttempt()
    {
        return $this->attempt;
    }

    /**
     * @return \App\CheckHandlers\CheckHandlerInterface[]
     */
    protected function getHandlers()
    {
        return app()->tagged('check_handler');
    }

    /**
     * @return bool
     * @throws CheckFailedException
     */
    public function handle()
    {
        foreach ($this->getHandlers() as $handler) {
            if ($handler->supports($this->check)) {
                return $this->perform($handler);
            }
        }

        throw new CheckFailedException($this->check, "No handler for " . $this->check->type);
    }

    /**
     * @param $handler
     * @return bool
     */
    protected function perform(CheckHandlerInterface $handler)
    {
        try {
            $handler->check($this->check, $this->release);
            event(new CheckEvent($this->check, $this->release));
            logger()->info(sprintf("Check #%s succeeded", $this->check->id));
            return true;
        } catch (CheckFailedException $e) {
            logger()->info(sprintf("Check #%s failed: %s", $this->check->id, $e->getMessage()));

            if ($this->attempt >= config('checks.max_attempts')) {
                event(new CheckEvent($this->check, $this->release, $e));
            } else {
                $this->requeue();
            }
        }

        return true;
    }

    /**
     * @return Check
     */
    public function getCheck()
    {
        return $this->check;
    }

    /**
     * Queue job with delay
     */
    private function requeue()
    {
        $this->attempt++;
        $this->dispatch($this);
    }

    /**
     * @param Queue $queue
     * @param $command
     */
    public function queue(Queue $queue, RunChecksCommand $command)
    {
        if ($command->getAttempt() > 1) {
            logger()->info(
                sprintf("Queueing check #%s after %s attempt", $command->getCheck()->id, $command->getAttempt())
            );
        }

        $queue->later(Carbon::now()->addSeconds(config('checks.attempt_delay')), $command);
    }
}
