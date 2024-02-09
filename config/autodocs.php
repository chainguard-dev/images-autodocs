<?php

declare(strict_types=1);

use App\Page\OverviewPage;
use App\Page\ProvenancePage;
use App\Page\TagsHistoryPage;
use App\Page\VariantsPage;

return [
    'autodocs' => [
        // Pages to Build
        'pages' => [
            OverviewPage::class,
            ProvenancePage::class,
            TagsHistoryPage::class,
            VariantsPage::class
        ],
        // Build Output Folder
        'output' => envconfig('AUTODOCS_OUTPUT', __DIR__.'/../workdir/output'),
        // Cache Folder - where to look for cache json files
        'cache_dir' => envconfig('AUTODOCS_CACHE', __DIR__.'/../workdir/cache'),
        // Where to find the JSON file containing the full list of images and tags
        'cache_images_file' => envconfig('AUTODOCS_IMAGES_CACHE', 'images-tags.json'),
        // Templates directory
        'templates_dir' => envconfig('AUTODOCS_TEMPLATES', __DIR__.'/../templates'),
        // String containing list of images to skip building docs for, separated by a colon
        'ignore_images' => config_unfurl(
            'AUTODOCS_IGNORE_IMAGES',
            'alpine-base:k3s-images:k3s-embedded:sdk:spire:musl-dynamic:nri-kube-events:nri-kubernetes:nri-prometheus:gcc-musl:source-controller:curl-dev:alpine-base'
        ),
        // Original files that should be used as base for changelog comparison
        'changelog' => envconfig('AUTODOCS_CHANGELOG', __DIR__.'/../workdir/original'),
        // File path where to save the changelog
        'changelog_output' => envconfig('AUTODOCS_CHANGELOG_OUTPUT', __DIR__.'/../workdir/changelog.md'), //where to save the changelog
        // Whether to remove unchanged files from output dir - useful for creating smaller diffs in pull requests. When turned off, all files will be committed with a lastMod date change.
        'discard_unchanged' => envconfig('AUTODOCS_DISCARD_UNCHANGED', "true") //when true, unchanged files are removed from output dir
    ]
];
