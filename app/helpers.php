<?php

declare(strict_types=1);

use Parsed\Content;

function config_unfurl(string $envKey, string $defaultValue): array
{
    return explode(":", envconfig($envKey, $defaultValue));
}

function frontmatter_update(string $field, string $value, array $articles): void
{
    foreach ($articles as $articleFile) {
        if ( ! is_file($articleFile['path'])) {
            continue;
        }
        $articleContent = file_get_contents($articleFile['path']);
        $article = new Content($articleContent);
        $article->parse(new \Parsed\ContentParser());
        $article->frontMatterSet($field, $value);
        $article->updateRaw();
        file_put_contents($articleFile['path'], $article->raw);
    }
}
