<?php

namespace App\Page;
use Autodocs\Page\ReferencePage;
use Minicli\FileNotFoundException;

class OverviewPage extends ReferencePage
{
    public string $image;
    public string $readme;

    public function loadData(array $parameters = []): void
    {
        $this->image = $parameters['image'];
        $sources = $this->autodocs->config['images_sources'];
        $readme = "";

        foreach ($sources as $sourcePath) {
            if (is_dir($sourcePath . '/' . $this->image)) {
                $readme = file_get_contents($sourcePath . '/' . $this->image . '/README.md');
                break;
            }
        }

        if ($readme == "") {
            //return a default template readme
            $readme = "Overview of $this->image Chainguard Image";
        }

        $readme = str_ireplace("# $readme", "", $readme);
        $readme = preg_replace('/<!--(.*)-->(.*)<!--(.*)-->/Uis', '', $readme);

        $this->readme = $readme;
    }

    public function getName(): string
    {
        return 'overview';
    }

    public function getSavePath(): string
    {
        return $this->image . '/overview.md';
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
