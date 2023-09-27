#!/usr/bin/env sh

export OUTPUT_PATH=workdir/cache

echo "Fetching variants config...";
for image in $(jq -r '.[].repo.name' ${OUTPUT_PATH}/images-tags.json); do
  echo '\n#######################################################\n'
  echo "Fetching latest tags info for ${image} image"
  echo '\n#######################################################\n'
  for tags in $(jq --arg image_repo $image -r 'map(select(.repo.name == $image_repo)) | [.[].tags[].name] | map(select(startswith("latest"))) | .[]' ${OUTPUT_PATH}/images-tags.json); do
    for tag in $tags; do
      cosign verify-attestation $(crane digest --full-ref --platform=linux/amd64 cgr.dev/chainguard/${image}:${tag}) \
        --certificate-identity="https://github.com/chainguard-images/images/.github/workflows/release.yaml@refs/heads/main" \
        --certificate-oidc-issuer="https://token.actions.githubusercontent.com" --type="https://apko.dev/image-configuration" \
        2>/dev/null | jq -rs '.[0].payload' | base64 -d > ${OUTPUT_PATH}/${image}.${tag}.json
    done
  done
done
echo "All finished 💅 ";
