<?php

namespace App;

use Autodocs\DataFeed\JsonDataFeed;
use Autodocs\Exception\NotFoundException;
use Autodocs\Mark;

class Image
{
    public string $name;

    public string $readmeDev = "";
    public string $readmeProd = "";
    public array $tagsDev = [];
    public array $tagsProd = [];
    public array $variants = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getJson(): string
    {
        return json_encode([
            'name' => $this->name,
            'tagsDev' => $this->tagsDev,
            'tagsProd' => $this->tagsProd,
            'readmeDev' => $this->readmeDev,
            'readmeProd' => $this->readmeProd,
            'variants' => $this->variants,
        ]);
    }

    public function getReadme(string $fallback): string
    {
        if ($this->readmeDev == "" or empty($this->readmeDev)) {
            if ($this->readmeProd == "" or empty ($this->readmeProd)) {
                return $fallback;
            }
            return $this->readmeProd;
        }
        return $this->readmeDev;
    }

    public function getRegistryTagsTable(): string
    {
        $tagsDev = $tagsProd = [];
        foreach ($this->tagsDev as $tag) {
            $tagsDev[] = $tag['name'];
        }
        foreach ($this->tagsProd as $tag) {
            $tagsProd[] = $tag['name'];
        }
        $rows = [
            ['`cgr.dev/chainguard`', count($tagsDev) ? implode(', ', $tagsDev) : "No public tags are available for this image."],
            ['`cgr.dev/chainguard-private`', count($tagsProd) ? implode(', ', $tagsProd) : "No production tags are available for this image."]
        ];

        return Mark::table($rows, ['Registry', 'Tags']);
    }

    /**
     * @throws NotFoundException
     */
    public static function loadFromDatafeed(string $datafeed): Image
    {
        $imageDatafeed = new JsonDataFeed();
        $imageDatafeed->loadFile($datafeed);
        $image = new Image($imageDatafeed->json['name']);

        $image->tagsDev = $imageDatafeed->json['tagsDev'];
        $image->tagsProd = $imageDatafeed->json['tagsProd'];
        $image->readmeDev = $imageDatafeed->json['readmeDev'];
        $image->readmeProd = $imageDatafeed->json['readmeProd'];
        $image->variants = $imageDatafeed->json['variants'];

        return $image;
    }
}
