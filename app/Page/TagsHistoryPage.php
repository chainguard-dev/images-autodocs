<?php

namespace App\Page;

use Autodocs\Mark;
use Autodocs\Page\ReferencePage;

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
        return $this->image . '/tags_history.md';
    }

    public function getContent(): string
    {
        return $this->autodocs->stencil->applyTemplate('image_tags_page', [
            'title' =>"$this->image Image Tags History",
            'description' => "Image Tags and History for the $this->image Chainguard Image",
            'content' => $this->getTagsTable(),
        ]);
    }

    public function orderTags(array $tag1, array $tag2): int
    {
        $date1 = new \DateTime($tag1['lastUpdated']);
        $date2 = new \DateTime($tag2['lastUpdated']);

        if ($date1 == $date2) {
            return 0;
        }

        return ($date1 < $date2) ? -1 : 1;
    }

    public function getTagsForImage(string $imageName)
    {
        try {
            $imagesList = $this->autodocs->getDataFeed('images-tags.json');
        } catch (\Exception $e) {
            return "";
        }

        foreach ($imagesList->json as $image) {
            if ($image['repo']['name'] === $imageName) {
                return $image['tags'];
            }
        }

        return [];
    }

    public function getTagsTable(array $onlyTags = [], $relativeTime = false): string
    {
        $imageTags = $this->getTagsForImage($this->image);

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
            $now = new \DateTime();
            $update = new \DateTime($tags[0]['lastUpdated']);
            $interval = $now->diff($update);

            //suppress tags older than 1 month
            if ($interval->m) {
                continue;
            }

            $tagsList = "";
            foreach ($tags as $tag) {
                //skip other tags when a set is provided
                if (count($onlyTags) AND !in_array($tag['name'], $onlyTags)) {
                    continue;
                }
                $tagsList .= ' `' . $tag['name'] . '`';
            }

            if ($tagsList != "") {
                $rows[] = [
                    $tagsList,
                    $update->format('F jS'),
                    "`$digest`"
                ];
            }
        }

        return Mark::table($rows, ['Tag (s)', 'Last Changed', 'Digest']);
    }
}
