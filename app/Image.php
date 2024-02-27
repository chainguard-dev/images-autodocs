<?php

namespace App;

class Image
{
    public string $name;

    public string $readmeDev = "";
    public array $tagsDev = [];
    public array $variantsDev = [];
    public string $readmeProd = "";
    public array $tagsProd = [];
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
}
