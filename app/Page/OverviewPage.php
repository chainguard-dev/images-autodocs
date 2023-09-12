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
        $readme = preg_replace('/<!--(.*)-->(.*)<!--(.*)-->/Uis', '', $readme);

        $this->readme = $readme;
    }

    public function findReadme(string $image): string
    {
        $readme = "";
        $sources = $this->autodocs->config['images_sources'];
        foreach ($sources as $sourcePath) {
            if (is_dir($sourcePath.'/'.$image)) {
                $readme = file_get_contents($sourcePath.'/'.$image.'/README.md');
                break;
            }
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
