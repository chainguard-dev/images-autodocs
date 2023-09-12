![Autobots Autodocs](workdir/autod.png)
# Images Autodocs
[![Build Chainguard Images Reference Docs](https://github.com/chainguard-dev/images-autodocs/actions/workflows/autodocs-images.yaml/badge.svg)](https://github.com/chainguard-dev/images-autodocs/actions/workflows/autodocs-images.yaml)

This repository contains automation pipelines to generate the [reference documentation for Chainguard Images(https://edu.chainguard.dev/chainguard/chainguard-images/reference/) in [Chainguard Academy](https://edu.chainguard.dev).

## GitHub Action
This application runs as a GitHub Action workflow, but can also be executed in a local environment as long as you prepare data sources accordingly.

### Customizing Action
The following environment variables should be used to customize how and where docs are built:

- `AUTODOCS_SOURCES` - directories where image sources (with READMEs) can be found. Multiple directories can be defined using a `:` as separator. Default: `./workdir/sources/images/images`.
- `AUTODOCS_OUTPUT` - directory where generated docs will be saved. Default: `./workdir/output`.
- `AUTODOCS_CACHE` - directory where cache files can be found. Default: `./workdir/cache`.
- `AUTODOCS_IMAGES_CACHE` - file where information about images and tags are saved. Default: `images-list.json`.
- `AUTODOCS_TEMPLATES` - directory where to find docs templates. Default to `./templates`.
- `AUTODOCS_IGNORE_IMAGES` - list of images to **not** build docs for. These will be skipped at build time. Image names should be listed as a string using `:` as separator.
- `AUTODOCS_CHANGELOG` - source directory to compare with new builds in order to generate a changelog. Typically, the same as `AUTODOCS_OUTPUT`.
- `AUTODOCS_CHANGELOG_FILE` - path to markdown file where to keep the changelog.

## Running Autodocs Locally

Before running autodocs locally, you'll need to prepare the data sources necessary to build the documentation. These include:

- Repository with Image source files: required to pull in image README files.
- JSON data feeds: these contain important information about images, tags history, installed packages, and more.

### 1. Preparing the Repository Sources
Start by cloning the image source repositories into the workdir:

```shell
cd workdir/sources
git clone git@github.com:chainguard-images/images.git
```
Multiple sources can be used, but they need to be specified in the `config/autodocs.php` configuration file (or as an environment variable). The path `workdir/sources/images` is added by default.

### 2. Preparing the JSON Data sources

The `bin` folder has two shell scripts that should be executed in order to save the data in the application's cache dir. You'll need to be authenticated with chainctl to run the scripts.

Start by authenticating with `chainctl`:

```shell
chainctl auth login chainctl auth login --identity [IDENTITY]
```

Then, fetch the list of images and tags. You'll need to provide the `IMAGES_GROUP` variable with a valid group to fetch the full list of images and their tags.

```shell
IMAGES_GROUP=[IMAGES_GROUP] ./bin/getImages.sh
```
Once you have the `images-tags.json` file containing all images and tags, you can run the second script to pull in all metadata from `latest-*` image variants. This data is required to generate the image variants pages. This script takes a while to complete (approximately 20 minutes). 

```shell
./bin/getVariants.sh
```
When finished, you should have the cache dir (workdir/cache) populated with JSON files, one for each image+variant.

### 3. Building the Docker Image

With all files in place, you can now build the included Docker image to run autodocs:

```shell
docker build . -t autodocs-images
```

### 4. Generating Docs
You can now generate image docs with:

```shell
docker run --rm -v ./workdir:/app/workdir autodocs-images build images
```
This will output all docs to `workdir/output` . Each image should have 4 pages:

```shell
workdir/output/php
├── image_specs.md
├── overview.md
├── provenance_info.md
└── tags_history.md
```

