<?php

declare(strict_types=1);

namespace App\Command\Build;

use App\ImageChangelog;
use App\Page\ChangelogPage;
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
        copy_recursive($autodocs->config['changelog'], $autodocs->config['output']);
        $changelog = new ImageChangelog($autodocs->config['output']);
        $changelog->capture();

        //get list of images
        $imagesList = new JsonDataFeed();
        try {
            $imagesList->loadFile($autodocs->config['cache_dir'].'/'.$autodocs->config['cache_images_file']);
        } catch (NotFoundException $exception) {
            $this->error("Cache file not found: ".$autodocs->config['cache_dir'].'/'.$autodocs->config['cache_images_file']);
            return;
        }

        if ($this->hasParam('image')) {
            $this->buildDocsForImage($this->getParam('image'));
        } else {
            foreach ($imagesList->json as $image) {
                $imageName = $image['repo']['name'];
                $this->buildDocsForImage($imageName);
            }
        }

        $changelog->makeDiff($autodocs->config['output']);
        $this->info("Finished building.");
        $this->success($changelog->getChangesSummary(), true);
        if ($changelog->hasChanges()) {
            $this->out("\nUpdating content timestamps...\n");
            $changelog->updateTimestamps();
            $this->out("\nBuilding changelog...\n");
            $changelogPage = new ChangelogPage($autodocs);
            $changelogPage->loadData(['newFiles' => $changelog->newFiles, 'changedFiles' => $changelog->changedFiles]);
            $saveChangelog = $changelogPage->getSavePath();
            $autodocs->storage->saveFile($saveChangelog, $changelogPage->getContent());
            $this->success("Changelog saved to {$saveChangelog}");
        }

        if ($autodocs->config['discard_unchanged']) {
            $this->out("\nCleaning Up...\n");
            $changelog->discardUnchanged();
        }
    }

    private function buildDocsForImage(string $imageName): void
    {
        $autodocs = $this->getApp()->autodocs;
        if (in_array($imageName, $autodocs->config['ignore_images'])) {
            $this->out("\nSkipping image in ignore list: {$imageName}...\n");
            return;
        }

        $this->info("Building docs for {$imageName}...");
        $autodocs->buildPages($this->getParam('pages') ?? "all", [
            'image' => $imageName
        ]);
    }
}
