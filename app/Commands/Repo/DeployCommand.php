<?php namespace App\Commands\Repo;

use App\Commands\Command;
use App\Model\Release;
use App\Model\Repo;
use App\Traits\ManagesDocker;
use Gitonomy\Git\Commit;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class DeployCommand
 */
class DeployCommand extends Command implements ShouldBeQueued, SelfHandling
{
    use ManagesDocker;
    use DispatchesJobs;

    /**
     * @var Repo
     */
    private $repo;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var ProcessBuilder
     */
    private $process;
    /**
     * @var string
     */
    private $revision;

    /**
     * @var Release
     */
    private $release;

    /**
     * @param Repo $repo
     * @param string $revision
     * @param null $releaseName
     */
    function __construct(Repo $repo, $revision = 'master', $releaseName = null)
    {
        $this->repo = $repo;
        $this->fs = new Filesystem();
        $this->process = new ProcessBuilder();
        $this->revision = $revision;
        $this->release = new Release($this->repo, $this->makeId($releaseName));
        $this->release->init($this->revision);
    }

    public function handle()
    {
        $release = $this->release;
        $releaseDir = $release->path();

        try {
            if (!$this->prepareDir()) {
                throw new \RuntimeException("Failed to delete old release dir $releaseDir");
            }

            if (!$this->fs->isDirectory($releaseDir) && !$this->fs->makeDirectory($releaseDir, 0755, true)) {
                throw new \RuntimeException("Failed to create release dir $releaseDir");
            }

            $release->status(Release::STATUS_INITIATING);
            $this->fs->put($this->release->path('RELEASE.rev'), $this->revision);

            $this->repo->get()->run("archive", [$this->revision, "-o", "$releaseDir/build.tar.gz"]);

            $process = ProcessBuilder::create(["tar", "-xvf", "$releaseDir/build.tar.gz"])
                ->setWorkingDirectory($releaseDir)
                ->getProcess();

            if ($code = $process->run() !== 0) {
                throw new \RuntimeException("Release failed, process returned $code: " . $process->getErrorOutput());
            }

            $this->fs->put($release->path('RELEASE'), "REVISION: {$this->revision}");
            $this->compose()->prepareYml($release);

            $this->dispatch(new UpCommand($release));
            $release->status(Release::STATUS_STARTING);
        } catch (\Exception $e) {
            $release->logger()->error("Error while deploy: " . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $release->status(Release::STATUS_ERROR);
            throw $e;
        }
    }

    /**
     * @param null|string $name
     * @return string
     */
    protected function makeId($name = null)
    {
        if (!empty($name)) {
            return $name;
        }

        $commit = $this->repo->get()->getCommit($this->revision);

        if ($commit instanceof Commit) {
            return $commit->getShortHash();
        }

        return $this->revision;
    }

    private function prepareDir()
    {
        foreach ($this->release->containers() as $container) {
            $this->containers()->stop($container);
        }

        return $this->fs->deleteDirectory($this->release->path());
    }
}
