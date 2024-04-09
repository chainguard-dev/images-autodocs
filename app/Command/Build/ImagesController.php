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
    /**
     * @throws NotFoundException
     */
    public function handle(): void
    {
        /** @var AutodocsService $autodocs */
        $autodocs = $this->getApp()->autodocs;
        copy_recursive($autodocs->config['changelog'], $autodocs->config['output']);
        $changelog = new ImageChangelog($autodocs->config['output']);
        $changelog->capture();

        $invalidImages = [];
        if ($this->hasParam('image')) {
            $this->buildDocsForImage($this->getParam('image'));
        } else {
            foreach (glob($autodocs->config['cache_dir'].'/datafeeds/*.json') as $imageCache) {
                $dataFeed = new JsonDataFeed();
                $dataFeed->loadFile($imageCache);
                $imageName = $dataFeed->json['name'];
                if (!count($dataFeed->json['tagsDev']) AND !count($dataFeed->json['tagsProd'])) {
                    $invalidImages[] = $imageName;
                    $this->info("Image has no tags, skipping...");
                    continue;
                }
                $this->buildDocsForImage($imageName);
            }
        }

        $changelog->makeDiff($autodocs->config['output']);
        $this->info("Finished building.");
        $this->success($changelog->getChangesSummary(), true);
        if (count($invalidImages)) {
            $this->info(sprintf("A total of %s images containing no tags were ignored: %s", count($invalidImages), implode(', ', $invalidImages)));
        }

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
        if (in_array($imageName, $autodocs->config['ignore_images']) || str_starts_with($imageName, "request-")) {
            $this->out("\nSkipping image in ignore list: {$imageName}...\n");
            return;
        }

        $this->info("Building docs for {$imageName}...");
        $autodocs->buildPages($this->getParam('pages') ?? "all", [
            'image' => $imageName
        ]);
    }
}
