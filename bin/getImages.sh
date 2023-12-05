#!/usr/bin/env sh

export OUTPUT_PATH=workdir/cache

echo "Getting images tags list...";
chainctl img ls -ojson > ${OUTPUT_PATH}/images-tags.json 2>&1
echo "Finished.";
