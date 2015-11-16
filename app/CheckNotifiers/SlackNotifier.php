<?php namespace App\CheckNotifiers;

use App\Events\CheckEvent;
use App\Model\Check;
use GuzzleHttp\Client;

/**
 * Class SlackNotifier
 */
class SlackNotifier implements CheckNotifierInterface
{
    /**
     * @var Client
     */
    private $client;

    function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param CheckEvent $event
     */
    public function success(CheckEvent $event)
    {
        if (!$this->enabled($event)) {
            return;
        }

        $repo = $event->getRelease()->repo();
        $this->postMessage([
            'text' => '*Release checks passed*',
            'channel' => $event->getCheck()->getParam('slack'),
            'attachments' => json_encode([
                [
                    'fallback' => 'Checks passed',
                    "color" => "good",
                    "title" => "Checks passed",
                    "title_link" => $this->url("view", $repo->id, "releases"),
                    "thumb_url" => $this->url("build/img/icon.png"),
                    'fields' => [
                        [
                            'title' => 'Project',
                            'value' => $repo->name,
                            'short' => true,
                        ],
                        [
                            'title' => 'Release',
                            'value' => $event->getRelease()->id(),
                            'short' => true,
                        ],
                        [
                            'title' => 'View release',
                            'value' => $event->getRelease()->domain(),
                            'short' => false,
                        ],
                    ]
                ]
            ])
        ]);
    }

    private function enabled(CheckEvent $event)
    {
        return $event->getCheck()->getParam('slack') != null;
    }

    /**
     * @return string
     */
    private function url()
    {
        return env("APP_URL") . '/' . implode('/', func_get_args());
    }

    /**
     * @param CheckEvent|Check $event
     */
    public function failure(CheckEvent $event)
    {
        if (!$this->enabled($event)) {
            return;
        }

        $repo = $event->getRelease()->repo();
        $this->postMessage([
            'text' => '*Release checks failed* http://' . $event->getRelease()->domain(),
            'channel' => $event->getCheck()->getParam('slack'),
            'attachments' => json_encode([
                [
                    'fallback' => 'Check failed: ' . $event->getException()->getMessage(),
                    "color" => "danger",
                    "title" => "Failure summary",
                    "title_link" => $this->url("view", $repo->id, "releases"),
                    "thumb_url" => $this->url("build/img/icon.png"),
                    'fields' => [
                        [
                            'title' => 'Project',
                            'value' => $repo->name,
                            'short' => true,
                        ],
                        [
                            'title' => 'Release',
                            'value' => $event->getRelease()->id(),
                            'short' => true,
                        ],
                        [
                            'title' => 'Error message',
                            'value' => $event->getException()->getMessage(),
                            'short' => false,
                        ],
                    ]
                ]
            ])
        ]);
    }

    /**
     * @param array $params
     */
    private function postMessage(array $params)
    {
        $this->client->get("https://slack.com/api/chat.postMessage", [
            'query' => $params + [
                'token' => env('SLACK_TOKEN'),
                'icon_url' => $this->url("build/img/icon.png"),
                'username' => env('APP_NAME', 'DogDep'),
            ]
        ]);

        logger()->info("Sending message to slack: " . $params['text'], $params);
    }
}
