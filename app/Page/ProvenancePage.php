<?php

declare(strict_types=1);

namespace App\Page;

use App\Image;
use Autodocs\Exception\NotFoundException;
use Autodocs\Page\ReferencePage;
use Minicli\FileNotFoundException;

class ProvenancePage extends ReferencePage
{
    public string $image;
    public function loadData(array $parameters = []): void
    {
        $this->image = $parameters['image'];
    }

    public function getName(): string
    {
        return 'provenance';
    }

    public function getSavePath(): string
    {
        return $this->image.'/provenance_info.md';
    }

    /**
     * @throws FileNotFoundException
     * @throws NotFoundException
     */
    public function getContent(): string
    {
        $image = Image::loadFromDatafeed($this->autodocs->config['cache_dir'] . '/datafeeds/' . $this->image . ".json");

        return $this->autodocs->stencil->applyTemplate('image_provenance_page', [
            'title' => $this->image,
            'description' => "Provenance information for {$this->image} Chainguard Image",
            'registryTags' => $image->getRegistryTagsTable()
        ]);
    }
}
