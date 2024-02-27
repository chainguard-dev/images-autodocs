<?php

namespace App;

class ImageCollection
{
    protected array $images = [];

    public function add(Image $image): void
    {
        $this->images[] = $image;
    }

    public function addImages(array $images): void
    {
        foreach ($images as $image) {
            $this->add($image);
        }
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function get(string $imageName, $createNew = false): ?Image
    {
        /** @var Image $image */
        foreach ($this->images as $image) {
            if ($image->getName() === $imageName) {
                return $image;
            }
        }

        if ($createNew) {
             return new Image($imageName);
        }

        return null;
    }
}
