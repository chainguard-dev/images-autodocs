<?php

namespace App\Page;

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
        return $this->image . '/provenance_info.md';
    }

    /**
     * @throws FileNotFoundException
     */
    public function getContent(): string
    {
        return $this->autodocs->stencil->applyTemplate('image_provenance_page', [
            'title' => $this->image,
            'description' => "Provenance information for $this->image Chainguard Image"
        ]);
    }
}
