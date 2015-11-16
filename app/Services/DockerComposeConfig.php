<?php namespace App\Services;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DockerComposeConfig
 */
class DockerComposeConfig implements \ArrayAccess
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param string $content
     */
    function __construct($content)
    {
        $this->config = Yaml::parse($content);
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->config;
    }

    /**
     * @param string $service
     * @param string $variable
     * @param string $value
     */
    public function setEnv($service, $variable, $value)
    {
        $this->config[$service]['environment'][$variable] = $value;
    }

    /**
     * @return array
     */
    public function getVolumes()
    {
        $volumes = [];
        foreach ($this->config as $service => $serviceConfig) {
            if (isset($serviceConfig['volumes'])) {
                foreach ($serviceConfig['volumes'] as $volume) {
                    $volumes[] = explode(':', $volume);
                }
            }
        }

        return $volumes;
    }

    /**
     * @{inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    /**
     * @{inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    /**
     * @{inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    /**
     * @{inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}
