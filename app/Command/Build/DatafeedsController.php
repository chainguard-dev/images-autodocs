<?php

declare(strict_types=1);

namespace App\Command\Build;

use App\ImageCollection;
use Autodocs\DataFeed\JsonDataFeed;
use Autodocs\Exception\NotFoundException;
use autodocs\Service\AutodocsService;
use Minicli\Command\CommandController;
use Exception;
use TypeError;

class DatafeedsController extends CommandController
{
    /**
     * @throws NotFoundException
     */
    public function handle(): void
    {
        /** @var AutodocsService $autodocs */
        $autodocs = $this->getApp()->autodocs;

        try {
            $imagesDevList = $autodocs->getDataFeed('images-tags-dev.json');
            $imagesProdList = $autodocs->getDataFeed('images-tags-prod.json');
        } catch (Exception $exception) {
            $this->error("Error: ".$exception->getMessage());
            return;
        } catch (TypeError $error) {
            $this->error("Error: ".$error->getMessage());
            return;
        }

        $cacheDev = $this->getApp()->autodocs->config['cache_dir'] . '/images-dev';
        $cacheProd = $this->getApp()->autodocs->config['cache_dir'] . '/images-prod';

        $images = new ImageCollection();
        foreach ($imagesDevList->json as $imageInfo) {
            $image = $images->get($imageInfo['repo']['name'], true);
            $image->tagsDev = $imageInfo['tags'];
            if (array_key_exists('readme',  $imageInfo['repo'])){
                $image->readmeDev = $imageInfo['repo']['readme'];
            }
            $image->variantsDev = $this->getImageConfigs($image->getName(), $cacheDev);
            $images->add($image);
            $autodocs->storage->saveFile($autodocs->config['cache_dir'] . '/datafeeds/' . $image->name . ".json", $image->getJson());
        }


        foreach ($imagesProdList->json as $imageInfo) {
            $image = $images->get($imageInfo['repo']['name'], true);
            $image->tagsProd = $imageInfo['tags'];
            if (array_key_exists('readme',  $imageInfo['repo'])){
                $image->readmeProd = $imageInfo['repo']['readme'];
            }
            $image->variantsProd = $this->getImageConfigs($image->getName(), $cacheProd);
            $images->add($image);
            $autodocs->storage->saveFile($autodocs->config['cache_dir'] . '/datafeeds/' . $image->name . ".json", $image->getJson());
        }

        $this->success("Finished building image Datafeeds based on cached data.");
    }

    /**
     * @throws NotFoundException
     */
    public function getImageConfigs(string $image, string $cachePath): array
    {
        $variantsList = [];

        foreach (glob($cachePath . '/*.json') as $imageConfig) {
            if (str_starts_with(basename($imageConfig), $image.'.latest')) {

                list($imageName, $variantName, $extension) = explode('.', basename($imageConfig));
                try {
                    $dataFeed = new JsonDataFeed();
                    $dataFeed->loadFile($imageConfig);
                    $variantsList[$variantName] = $dataFeed->json;
                } catch (TypeError $e) {
                    //json might have issues. skip
                }
            }
        }

        return $variantsList;
    }
}
