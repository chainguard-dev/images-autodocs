<?php

namespace App\Command\Build;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->info("To build docs for all images, run:");
        $this->out("./autodocs build images\n");

        $this->info("To build docs for a specific image, run:");
        $this->out("./autodocs build images image=php\n");

        $this->info("To build only a subset of doc pages, run:");
        $this->out("./autodocs build images pages=overview,tags\n");
    }
}
