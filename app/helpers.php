<?php

declare(strict_types=1);

use Parsed\Content;

function config_unfurl(string $envKey, string $defaultValue): array
{
    return explode(":", envconfig($envKey, $defaultValue));
}

function frontmatter_update(string $field, string $value, array $articles)
{
    foreach ($articles as $articlePath) {
        if (!is_file($articlePath)) {
            continue;
        }
        $articleContent = file_get_contents($articlePath);
        $article = new Content($articleContent);

        $article->frontMatterSet($field, $value);
        $article->updateRaw();
        file_put_contents($articlePath, $article->raw);
    }
}
