<?php namespace App\CheckHandlers;

use App\Exceptions\CheckFailedException;
use App\Model\Check;
use App\Model\Release;
use App\Traits\ManagesDocker;

/**
 * Class CommandCheckHandler
 */
class CommandCheckHandler implements CheckHandlerInterface
{
    use ManagesDocker;

    /**
     * @param Check $check
     * @return bool
     */
    public function supports(Check $check)
    {
        return $check->type == "command";
    }

    /**
     * @param Check $check
     * @param Release $release
     * @return string
     * @throws \App\Exceptions\CheckFailedException
     */
    public function check(Check $check, Release $release)
    {
        $container = $release->containers($check->container);

        if (!$container) {
            throw new CheckFailedException(sprintf("Container %s not found!", $check->container));
        }

        $cmd = $check->getParam('command');
        $execId = $this->containers()->exec($container, explode(' ', $cmd));
        if (!$execId) {
            throw new CheckFailedException("Creating exec failed!");
        }

        $output = $this->containers()->execstart($execId);
        $result = $this->containers()->execInspect($execId);

        $exitCode = $result['ExitCode'];
        if ($exitCode == 0) {
            return;
        }

        throw new CheckFailedException(
            sprintf("Command %s failed (exit code %s): %s", $cmd, $exitCode, (string) $output->getOutput())
        );
    }
}
