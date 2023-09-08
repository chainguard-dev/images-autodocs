<?php

declare(strict_types=1);

namespace App\Command\Notify;

use Minicli\Command\CommandController;

class PullRequestController extends CommandController
{
    public function handle(): void
    {
        $message = "A new autodocs pull request has been submitted and awaits review.";

        $url = getenv('PR_URL') ?? "https://github.com/chainguard-dev/edu/pulls";
        $message .= "\nPull request URL: ".$url;

        if (getenv('PR_SUMMARY')) {
            $message .= "\n".getenv('PR_SUMMARY');
        }

        $this->getApp()->runCommand(['autodocs', 'notify', 'slack', 'message='.$message]);
    }
}
