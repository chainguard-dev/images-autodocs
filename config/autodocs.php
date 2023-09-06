<?php

function unflatten(string $config): array
{
    return explode(":", $config);
}

return [
    'autodocs' => [
        'images_sources' => unflatten(envconfig('AUTODOCS_SOURCES', __DIR__ . '/../workdir/sources/images/images')),
        'pages' => [
            \App\Page\OverviewPage::class,
            \App\Page\ProvenancePage::class,
            \App\Page\TagsHistoryPage::class,
            \App\Page\VariantsPage::class
        ],
        'output' => envconfig('AUTODOCS_OUTPUT', __DIR__ . '/../workdir/output'),
        'cache_dir' => envconfig('AUTODOCS_CACHE', __DIR__ . '/../workdir/cache'),
        'cache_images_file' => envconfig('AUTODOCS_IMAGES_CACHE', 'images-tags.json'),
        'templates_dir' => envconfig('AUTODOCS_TEMPLATES', __DIR__ . '/../templates'),
        'ignore_images' => unflatten(envconfig('AUTODOCS_IGNORE_IMAGES',
            'alpine-base:k3s-images:sdk:spire:musl-dynamic:nri-kube-events:nri-kubernetes:nri-prometheus:gcc-musl'
        )),
    ]
];
