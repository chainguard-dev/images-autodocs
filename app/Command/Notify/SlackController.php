<?php

declare(strict_types=1);

namespace App\Command\Notify;

use App\SlackNotifier;
use Minicli\Command\CommandController;
use Exception;

class SlackController extends CommandController
{
    public function handle(): void
    {
        if (null === getenv('AUTODOCS_SLACK_PRIMARY')) {
            throw new Exception("Missing AUTODOCS_SLACK_PRIMARY environment variable with channel endpoint.");
        }

        $notifier = new SlackNotifier([
            'primary' => getenv('AUTODOCS_SLACK_PRIMARY'),
            'secondary' => getenv('AUTODOCS_SLACK_SECONDARY'),
            'general' => getenv('AUTODOCS_SLACK_GENERAL'),
        ]);

        if ( ! $this->hasParam("message")) {
            throw new Exception("You must provide a message parameter with your notification message.");
        }

        $message = $this->getParam("message");
        $response = $notifier->send($message, $this->getParam('channel') ?? 'primary');

        if (200 !== $response['code']) {
            throw new Exception("There was an error with the request.");
        }

        $this->success("Message sent.");
    }
}
