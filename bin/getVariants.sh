#!/usr/bin/env sh

export OUTPUT_PATH=workdir/cache

echo "Fetching variants config...";
for image in $(jq -r '.[].repo.name' ${OUTPUT_PATH}/images-tags.json); do
  echo '\n#######################################################\n'
  echo "Fetching latest tags info for ${image} image"
  for tags in $(jq --arg image_repo $image -r 'map(select(.repo.name == $image_repo)) | [.[].tags[].name] | map(select(startswith("latest"))) | .[]' ${OUTPUT_PATH}/images-tags.json); do
    for tag in $tags; do
      cosign download attestation --platform=linux/amd64 --predicate-type=https://apko.dev/image-configuration \
      cgr.dev/chainguard/${image}:${tag} 2>/dev/null | jq -r '.payload' | base64 -d > ${OUTPUT_PATH}/${image}.${tag}.json
    done
  done
done
echo "All finished 💅 ";
