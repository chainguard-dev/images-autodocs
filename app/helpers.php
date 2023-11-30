<?php

declare(strict_types=1);

use Parsed\Content;
use Parsed\ContentParser;

function config_unfurl(string $envKey, string $defaultValue): array
{
    return explode(":", envconfig($envKey, $defaultValue));
}

function frontmatter_update(array $fields, array $values, array $articles): void
{
    foreach ($articles as $articleFile) {
        if ( ! is_file($articleFile['path'])) {
            continue;
        }
        $articleContent = file_get_contents($articleFile['path']);
        $article = new Content($articleContent);
        $article->parse(new ContentParser());
        foreach ($fields as $index => $field) {
            $article->frontMatterSet($field, $values[$index]);
        }

        $article->updateRaw();
        file_put_contents($articleFile['path'], $article->raw);
    }
}
