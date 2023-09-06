<?php

namespace App\Command\Build;

use Autodocs\DataFeed\JsonDataFeed;
use Autodocs\Exception\NotFoundException;
use autodocs\Service\AutodocsService;
use Minicli\Command\CommandController;

class ImagesController extends CommandController
{
    public function handle(): void
    {
        /** @var AutodocsService $autodocs */
        $autodocs = $this->getApp()->autodocs;

        //get list of images
        $imagesList = new JsonDataFeed();
        try {
            $imagesList->loadFile($autodocs->config['cache_dir'] . '/' . $autodocs->config['cache_images_file']);
        } catch (NotFoundException $exception) {
            $this->error("Cache file not found: " . $autodocs->config['cache_dir'] . '/' . $autodocs->config['cache_images_file']);
            return;
        }

        foreach ($imagesList->json as $image) {
            $imageName = $image['repo']['name'];
            if (in_array($imageName, $autodocs->config['ignore_images'])) {
                $this->out("\nSkipping image in ignore list: $imageName...\n");
                continue;
            }

            $this->info("Building docs for $imageName...");
            $autodocs->buildPages($this->getParam('pages') ?? "all", [
                'image' => $imageName
            ]);
        }
    }
}
