<?php

declare(strict_types=1);

namespace App\Page;

use Autodocs\Page\ReferencePage;
use Minicli\FileNotFoundException;

class OverviewPage extends ReferencePage
{
    public string $image;

    public function loadData(array $parameters = []): void
    {
        $this->image = $parameters['image'];
    }

    /**
     * @throws FileNotFoundException
     */
    public function findReadme(string $image): string
    {
        $imagesList = $this->autodocs->getDataFeed($this->autodocs->config['cache_images_file']);
        foreach ($imagesList->json as $imageInfo) {
            if ($imageInfo['repo']['name'] === $image) {
                if (array_key_exists('readme', $imageInfo['repo'])) {
                    return $imageInfo['repo']['readme'];
                }

                //readme not defined, try to find from annotation
                $imageMeta = $this->autodocs->getDataFeed("$image.latest.json");
                $image_source = $imageMeta->json["predicate"]["annotations"]["org.opencontainers.image.source"];
                $image_source = explode("/", $image_source);
                $referenceImage = end($image_source);
                if ($referenceImage != $image) {
                    $this->findReadme($referenceImage);
                }
            }
        }

        //no readme found, return fallback template
        return $this->autodocs->stencil->applyTemplate('image_overview_fallback', [
            'image' => $this->image
        ]);
    }

    public function getName(): string
    {
        return 'overview';
    }

    public function getSavePath(): string
    {
        return $this->image.'/_index.md';
    }

    /**
     * @throws FileNotFoundException
     */
    public function getContent(): string
    {
        $readme = $this->findReadme($this->image);

        $readme = str_ireplace("# {$readme}", "", $readme);
        $readme = preg_replace('/<!--monopod:start-->(.*)<!--monopod:end-->/Uis', '', $readme);

        return $this->autodocs->stencil->applyTemplate('image_overview', [
            'title' => $this->image,
            'content' => $readme
        ]);
    }
}
