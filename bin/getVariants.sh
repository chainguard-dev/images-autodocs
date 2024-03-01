#!/usr/bin/env sh

export OUTPUT_PATH=workdir/cache

echo "Fetching image variants: Production Images";
for image in $(jq -r '.[].repo.name' ${OUTPUT_PATH}/images-tags-prod.json); do
  echo '#######################################################'
  echo "Fetching config and variants info for ${image} image"
  for tags in $(jq --arg image_repo "$image" -r 'map(select(.repo.name == $image_repo)) | [.[].tags[].name] | map(select(startswith("latest"))) | .[]' ${OUTPUT_PATH}/images-tags-prod.json); do
    for tag in $tags; do
      PAYLOAD=$(cosign download attestation --platform=linux/amd64 --predicate-type=https://apko.dev/image-configuration \
      cgr.dev/chainguard-private/"${image}":"${tag}" 2>/dev/null | jq -r '.payload' | base64 -d)
      echo "$PAYLOAD" > ${OUTPUT_PATH}/images-prod/"${image}"."${tag}".json
    done
  done
done
echo "All finished 💅 ";
