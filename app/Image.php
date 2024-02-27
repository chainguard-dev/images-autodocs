<?php

namespace App;

use Autodocs\DataFeed\JsonDataFeed;
use Autodocs\Exception\NotFoundException;

class Image
{
    public string $name;

    public string $readmeDev = "";
    public string $readmeProd = "";
    public array $tagsDev = [];
    public array $tagsProd = [];
    public array $variantsDev = [];
    public array $variantsProd = [];

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
            'variantsDev' => $this->variantsDev,
            'variantsProd' => $this->variantsProd
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
        $image->variantsDev = $imageDatafeed->json['variantsDev'];
        $image->variantsProd = $imageDatafeed->json['variantsProd'];

        return $image;
    }
}
