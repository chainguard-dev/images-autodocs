<?php

declare(strict_types=1);

namespace App;

use Autodocs\Changelog;
use Parsed\Content;
use Parsed\ContentParser;

class ImageChangelog extends Changelog
{
    protected array $unchangedFiles;

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

    public function makeDiff(?string $monitoredPath = null): void
    {
        if ( ! $monitoredPath) {
            $monitoredPath = $this->monitoredPath;
        }
        $previous = $this->monitoredFiles;
        $this->registerFiles($monitoredPath);

        foreach ($this->monitoredFiles as $index => $file) {
            if ( ! array_key_exists($index, $previous)) {
                $this->newFiles[] = $file;
                continue;
            }

            if ($previous[$index]['md5'] !== $file['md5']) {
                $this->changedFiles[] = $file;
            }

            $this->unchangedFiles[] = $file;
        }
    }

    public function updateTimestamps(): void
    {
        $now = date('Y-m-d H:i:s');
        frontmatter_update(['date', 'lastmod'], [$now, $now], $this->newFiles);
        frontmatter_update(['lastmod'], [$now], $this->changedFiles);
    }

    public function discardUnchanged(): void
    {
        foreach ($this->unchangedFiles as $file) {
            if (is_dir($file['path'])) {
                rmdir($file['path']);
            }

            if (is_file($file['path'])) {
                unlink($file['path']);
            }
        }
    }
}
