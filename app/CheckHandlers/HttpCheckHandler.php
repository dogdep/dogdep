<?php namespace App\CheckHandlers;

use App\Docker\Port;
use App\Exceptions\CheckFailedException;
use App\Model\Check;
use App\Model\Release;

/**
 * Class HttpCheckHandler
 */
class HttpCheckHandler implements CheckHandlerInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    /**
     * @param Check $check
     * @return bool
     */
    public function supports(Check $check)
    {
        return $check->type == "http";
    }

    /**
     * @param Check $check
     * @param Release $release
     * @throws CheckFailedException
     */
    public function check(Check $check, Release $release)
    {
        $container = $release->containers($check->container);
        if (!$container) {
            throw new CheckFailedException(sprintf("Container %s not found!", $check->container));
        }

        /** @var Port $port */
        $port = $container->getMappedPort(80);

        if (!$port || !$port->getHostPort()) {
            throw new CheckFailedException(sprintf("Container %s port 80 is closed!", $check->container));
        }

        $url = $this->makeUrl($check, $port);

        try {
            $response = $this->client->get($url);
        } catch (\Exception $e) {
            throw new CheckFailedException($e->getMessage());
        }


        $pattern = $check->getParam('text');
        if (!empty($pattern) && !@preg_match("#$pattern#", (string)$response->getBody())) {
            throw new CheckFailedException(sprintf("Text %s not found in page %s", $pattern, $url));
        }
    }

    /**
     * @param Check $check
     * @param Port $port
     * @return string
     */
    private function makeUrl(Check $check, Port $port)
    {
        $url = 'http://' . env('APP_DOMAIN') . ':' . $port->getHostPort() . '/';

        $checkUrl = isset($check->params['url']) ? ltrim($check->params['url'], '/') : '';

        return $url . $checkUrl;
    }
}
