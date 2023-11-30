<?php

declare(strict_types=1);

namespace App;

use Autodocs\Changelog;
use Parsed\Content;
use Parsed\ContentParser;

class ImageChangelog extends Changelog
{
    public function registerFiles(string $monitoredPath): void
    {
        $contentParser = new ContentParser();
        foreach (glob($monitoredPath.'/*') as $filename) {
            if (is_dir($filename)) {
                $this->registerFiles($filename);
            }

            $index = $content_md5 = md5($filename);
            if ( ! is_dir($filename)) {
                $article = new Content(file_get_contents($filename));
                $article->parse($contentParser);
                $content_md5 = hash('sha256', $article->body_markdown);
            }


            $this->monitoredFiles[$index] = [
                'path' => $filename,
                'isDir' => is_dir($filename) ? "yes" : "no",
                'md5' => $content_md5,
            ];
        }
    }
}
