<?php

declare(strict_types=1);

namespace App\Page;

use App\Image;
use Autodocs\Exception\NotFoundException;
use Autodocs\Mark;
use Autodocs\Page\ReferencePage;
use Exception;
use DateTime;
use Minicli\FileNotFoundException;

class TagsHistoryPage extends ReferencePage
{
    public string $image;
    public function loadData(array $parameters = []): void
    {
        $this->image = $parameters['image'];
    }

    public function getName(): string
    {
        return 'tags';
    }

    public function getSavePath(): string
    {
        return $this->image.'/tags_history.md';
    }

    /**
     * @throws FileNotFoundException
     * @throws NotFoundException
     */
    public function getContent(): string
    {
        $image = Image::loadFromDatafeed($this->autodocs->config['cache_dir'] . '/datafeeds/' . $this->image . ".json");

        return $this->autodocs->stencil->applyTemplate('image_tags_page', [
            'title' => $this->image,
            'description' => "Image Tags and History for the {$this->image} Chainguard Image",
            'developer_tags' => count($image->tagsDev) ? $this->getTagsTable($image->tagsDev) : "Currently, there are no Developer versions of this image available.",
            'production_tags' => count($image->tagsProd) ? $this->getTagsTable($image->tagsProd) : "Currently, there are no Production versions of this image available.",
        ]);
    }

    public function orderTags(array $tag1, array $tag2): int
    {
        $date1 = new DateTime($tag1['lastUpdated']);
        $date2 = new DateTime($tag2['lastUpdated']);

        if ($date1 === $date2) {
            return 0;
        }

        return ($date1 < $date2) ? -1 : 1;
    }

    public function getTagsTable(array $imageTags, array $onlyTags = [], $relativeTime = false): string
    {
        usort($imageTags, [TagsHistoryPage::class, "orderTags"]);
        $imageTags = array_reverse($imageTags);

        //group by digest
        $groupedTags = [];
        foreach ($imageTags as $imageTag) {
            $groupedTags[$imageTag['digest']][] = [
                'lastUpdated' => $imageTag['lastUpdated'],
                'name' => $imageTag['name']
            ];
        }

        //prepare table
        $rows = [];
        foreach ($groupedTags as $digest => $tags) {
            $now = new DateTime();
            $update = new DateTime($tags[0]['lastUpdated']);
            $interval = $now->diff($update);

            //suppress tags older than 1 month
            if ($interval->m) {
                continue;
            }

            $tagsList = "";
            foreach ($tags as $tag) {
                //skip other tags when a set is provided
                if (count($onlyTags) && ! in_array($tag['name'], $onlyTags)) {
                    continue;
                }
                $tagsList .= ' `'.$tag['name'].'`';
            }

            if ("" !== $tagsList) {
                $rows[] = [
                    $tagsList,
                    $update->format('F jS'),
                    "`{$digest}`"
                ];
            }
        }

        return Mark::table($rows, ['Tag (s)', 'Last Changed', 'Digest']);
    }
}
