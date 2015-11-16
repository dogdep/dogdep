<?php namespace App\Services;

use App\Model\Release;
use App\Traits\UsesFilesystem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DockerCompose
 */
class DockerCompose
{
    use UsesFilesystem;
    use DispatchesJobs;

    const CONFIG = "dtool.yml";

    /**
     * @var null|string
     */
    private $socket;

    /**
     * @param string|null $socket
     */
    function __construct($socket = null)
    {
        $this->socket = is_null($socket) ? config('docker.socket') : $socket;
    }

    /**
     * @param Release $release
     * @param array $cmd
     */
    public function run(Release $release, array $cmd)
    {
        $cmdline = array_merge(['docker-compose', '-f', self::CONFIG, '-p', $release->name()], $cmd);
        $process = ProcessBuilder::create($cmdline)
            ->setEnv('DOCKER_HOST', $this->socket)
            ->setWorkingDirectory($release->path())
            ->enableOutput()
            ->setTimeout(0)
            ->getProcess();

        $log = $release->logger();
        $log->info("Starting compose command", ['command'=>$process->getCommandLine()]);

        $process->start();
        while ($process->isRunning()) {
            $this->log($release, $process->getIncrementalOutput());
            $this->log($release, $process->getIncrementalErrorOutput());
            sleep(1);
        }

        $this->log($release, $process->getErrorOutput());
        $this->log($release, $process->getOutput());
        $log->info(sprintf("Process exited with status %s", $process->getExitCode()));

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Failed to execute repo command: " . $process->getErrorOutput());
        }
    }

    /**
     * @param Release $release
     * @param string $envFile
     */
    protected function prepareEnvFile(Release $release, $envFile)
    {
        $envFile = $release->path($envFile);

        foreach (["$envFile.example", "$envFile.sample", "$envFile.dist"] as $candidate) {
            if (!$this->fs()->exists($envFile) && $this->fs()->exists($candidate)) {
                $this->fs()->copy($candidate, $envFile);
                return;
            }
        }
    }

    /**
     * @param Release $release
     * @param $output
     */
    private function log(Release $release, $output)
    {
        foreach (explode("\n", $output) as $line) {
            if (!empty($line)) {
                $release->logger()->info($line);
            }
        }
    }

    /**
     * @param Release $release
     */
    public function prepareYml(Release $release)
    {
        $fs = $this->fs();
        $config = $this->config($release);

        foreach ($config as $s => $service) {
            if (isset($service['ports']) && is_array($service['ports'])) {
                foreach ($service['ports'] as $i => $portSpec) {
                    $config[$s]['ports'][$i] = ltrim(substr($portSpec, strpos($portSpec, ':')), ':');
                    if ($config[$s]['ports'][$i] == 80) {
                        $config[$s]['environment']['VIRTUAL_HOST'] = $release->domain($s);
                    }
                }
            }

            if (isset($service['env_file'])) {
                foreach ((array) $service['env_file'] as $envFile) {
                    $this->prepareEnvFile($release, $envFile);
                }
            }

            if (isset($service['volumes']) && is_array($service['volumes'])) {
                foreach ($service['volumes'] as $i => $volSpec) {
                    if (starts_with($volSpec, '..')) {
                        throw new \RuntimeException("Paths outside of same directory are not supported in volumes!");
                    }

                    if (starts_with($volSpec, '/')) {
                        throw new \RuntimeException("Absolute paths are not supported in volumes!");
                    }

                    if (starts_with($volSpec, '.')) {
                        $volSpec = substr($volSpec, 1);
                    }

                    $config[$s]['volumes'][$i] = $release->remotePath() . "/current/" . ltrim($volSpec, '/\\');
                }
            }

            if (!isset($config[$s]['environment'])) {
                $config[$s]['environment'] = [];
            }

            $config[$s]['environment'] = $config[$s]['environment'] + $release->repo()->env;

            $config[$s]['labels'][] = "com.dtool.project:" . $release->repo()->name;
            $config[$s]['labels'][] = "com.dtool.release:" . $release->id();
        }

        foreach ($release->volumes() as $volume) {
            if (isset($config[$volume->container])) {
                $containerConf = $config[$volume->container];
                $mount = $volume->getMountPath($release->remotePath());

                // remove duplicate volumes if it already exists
                if (isset($containerConf['volumes'])) {
                    foreach ($containerConf['volumes'] as $k => $existing) {
                        if (substr($existing, strpos($existing, ':') + 1) == $mount[1]) {
                            unset($containerConf['volumes'][$k]);
                        }
                    }
                }

                $containerConf['volumes'][] = implode(':', $mount);
                $config[$volume->container]['volumes'] = array_values($containerConf['volumes']);
            }
        }

        $fs->put($release->path(self::CONFIG), Yaml::dump($config));
    }

    /**
     * @param Release $release
     * @return array
     */
    public function config(Release $release)
    {
        $ymlPath = $release->ymlPath();

        if (!is_file($ymlPath) || !is_readable($ymlPath)) {
            throw new \RuntimeException("$ymlPath does not exist or is not readable!");
        }

        return Yaml::parse($ymlPath);
    }
}
