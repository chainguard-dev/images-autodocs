<?php

declare(strict_types=1);

namespace App\Command\Build;

use App\ImageChangelog;
use App\Page\ChangelogPage;
use Autodocs\DataFeed\JsonDataFeed;
use Autodocs\Exception\NotFoundException;
use autodocs\Service\AutodocsService;
use Minicli\Command\CommandController;
use Exception;
use TypeError;

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

        /*
        try {
            $imagesList = $autodocs->getDataFeed($autodocs->config['cache_images_file']);
        } catch (Exception $exception) {
            $this->error("Error: ".$exception->getMessage());
            return;
        } catch (TypeError $error) {
            $this->error("Error: ".$error->getMessage());
            return;
        }*/


        if ($this->hasParam('image')) {
            $this->buildDocsForImage($this->getParam('image'));
        } else {
            foreach (glob($autodocs->config['cache_dir'].'/datafeeds/*.json') as $imageCache) {
                $dataFeed = new JsonDataFeed();
                $dataFeed->loadFile($imageCache);
                $imageName = $dataFeed->json['name'];
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
