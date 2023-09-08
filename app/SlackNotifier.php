<?php

declare(strict_types=1);

namespace App;

use Minicli\Curly\Client;

class SlackNotifier
{
    public Client $client;
    public array $channels;

    /**
     * ex: $channels['docs'] = unique_hook_per_channel
     * @param array $channels
     * @return void
     */
    public function __construct(array $channels)
    {
        $this->channels = $channels;
        $this->client = new Client();
    }

    public function send(string $message, string $channel): array
    {
        $slackEndpoint = $this->channels[$channel];

        return $this->client->post(
            $slackEndpoint,
            ['text' => $message, 'channelData' => 'mrkdwn'],
            ['Content-type: application/json']
        );
    }
}
