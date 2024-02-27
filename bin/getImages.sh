#!/usr/bin/env sh

export OUTPUT_PATH=workdir/cache
export GROUP_PUBLIC=720909c9f5279097d847ad02a2f24ba8f59de36a
export GROUP_PRIVATE=ce2d1984a010471142503340d670612d63ffb9f6
echo "Fetching developer images list...";
chainctl img ls -ojson --group=$GROUP_PUBLIC > ${OUTPUT_PATH}/images-tags-dev.json 2>&1
echo "Fetching production images list...";
chainctl img ls -ojson --group=$GROUP_PRIVATE > ${OUTPUT_PATH}/images-tags-prod.json 2>&1
echo "Finished.";
