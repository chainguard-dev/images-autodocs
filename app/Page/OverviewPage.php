<?php

declare(strict_types=1);

namespace App\Page;

use App\Image;
use Autodocs\Exception\NotFoundException;
use Autodocs\Page\ReferencePage;
use Minicli\FileNotFoundException;

class OverviewPage extends ReferencePage
{
    public string $image;

    public function loadData(array $parameters = []): void
    {
        $this->image = $parameters['image'];
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
     * @throws FileNotFoundException|NotFoundException
     */
    public function getContent(): string
    {
        $image = Image::loadFromDatafeed($this->autodocs->config['cache_dir'].'/datafeeds/'.$this->image.".json");
        $fallback = $this->autodocs->stencil->applyTemplate('image_overview_fallback', [
            'image' => $this->image
        ]);
        $readme = $image->getReadme($fallback);

        // removes header and monopod section
        $readme = str_ireplace("# {$readme}", "", $readme);
        $readme = preg_replace('/<!--monopod:start-->(.*)<!--monopod:end-->/Uis', '', $readme);

        $download = $this->autodocs->stencil->applyTemplate('image_overview_download', [
            'registry' => count($image->tagsDev) ? 'cgr.dev/chainguard' : 'cgr.dev/chainguard-private',
            'image' => $this->image
        ]);
        // overwrites download section
        $readme = preg_replace('/<!--getting:start-->(.*)<!--getting:end-->/Uis', $download, $readme);

        return $this->autodocs->stencil->applyTemplate('image_overview', [
            'title' => $this->image,
            'content' => $readme
        ]);
    }
}
