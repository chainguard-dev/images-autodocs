<?php

declare(strict_types=1);

namespace App\Page;

use Autodocs\Page\ReferencePage;
use Minicli\FileNotFoundException;

class OverviewPage extends ReferencePage
{
    public string $image;
    public string $readme;

    /**
     * @throws FileNotFoundException
     */
    public function loadData(array $parameters = []): void
    {
        $this->image = $parameters['image'];
        $readme = $this->findReadme($this->image);

        if ("" === $readme) {
            $readme = $this->autodocs->stencil->applyTemplate('image_overview_fallback', [
                'image' => $this->image
            ]);
        }

        $readme = str_ireplace("# {$readme}", "", $readme);
        $readme = preg_replace('/<!--monopod:start-->(.*)<!--monopod:end-->/Uis', '', $readme);

        $this->readme = $readme;
    }

    public function findReadme(string $image): string
    {
        $dataFeeds = $this->autodocs->dataFeeds;
        $readme = "";
        $sources = $this->autodocs->config['images_sources'];
        foreach ($sources as $sourcePath) {
            if (is_dir($sourcePath.'/'.$image)) {
                $readme = file_get_contents($sourcePath.'/'.$image.'/README.md');
                break;
            }
            # didn't find an image:readme 1:1 directory mapping, so look at image annotation for correct dir
            $fName = $image.".latest.json";
            $dataFeeds[$fName]->loadFile($this->autodocs->config['cache_dir'].'/'.$fName);
            $image_source = $dataFeeds[$fName]->json["predicate"]["annotations"]["org.opencontainers.image.source"];
            $image_source = explode("/", $image_source);
            $image = end($image_source);
            $readme = file_get_contents($sourcePath.'/'.$image.'/README.md');
        }
        return $readme;
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
        return $this->autodocs->stencil->applyTemplate('image_overview', [
            'title' => $this->image,
            'content' => $this->readme
        ]);
    }
}
