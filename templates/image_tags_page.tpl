---
title: "{{ title }} Image Tags History"
type: "article"
unlisted: true
description: "{{ description }}"
date: 2023-06-22T11:07:52+02:00
lastmod: 2023-06-22T11:07:52+02:00
draft: false
tags: ["Reference", "Chainguard Images", "Product"]
images: []
weight: 700
toc: true
---

{{< tabs >}}
{{< tab title="Overview" active=false url="/chainguard/chainguard-images/reference/{{ title }}/" >}}
{{< tab title="Variants" active=false url="/chainguard/chainguard-images/reference/{{ title }}/image_specs/" >}}
{{< tab title="Tags History" active=true url="/chainguard/chainguard-images/reference/{{ title }}/tags_history/" >}}
{{< tab title="Provenance" active=false url="/chainguard/chainguard-images/reference/{{ title }}/provenance_info/" >}}
{{</ tabs >}}

The following tables contains the most recent tags and digests that can be used to pin your Dockerfile to a specific build of this image. Check our guide on [Using the Tag History API](/chainguard/chainguard-images/using-the-tag-history-api/) for information on how to fetch all tags from an image and how to pin your Dockerfile to a specific digest.

Please note that digests and timestamps only change when there is a change to the image, even though images are rebuilt every night. The "Last Changed" column indicates when the image was last modified, and doesn't always reflect the latest build timestamp. For more information about how our reproducible builds work, please refer to [this blog post](https://www.chainguard.dev/unchained/reproducing-chainguards-reproducible-image-builds).

### Public Registry
The Public Registry contains our **Developer Images**, which typically comprise the `latest*` versions of an image.

{{ developer_tags }}

### Private/Dedicated Registry
The Private/Dedicated Registry contains our **[Production Images](https://www.chainguard.dev/chainguard-images)**, which include all versioned tags of an image and special images that are not available in the public registry (including FIPS images and other custom builds).

{{ production_tags }}
