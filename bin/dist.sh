#!/usr/bin/env bash

BUILD_VERSION=$(node -pe "require('./package.json').version")
export BUILD_VERSION
BUILD_NAME=$(node -pe "require('./package.json').name")
export BUILD_NAME

if [ ! -d "dist" ]; then
  mkdir "dist"
fi

if [ ! -d "artifact" ]; then
  mkdir "artifact"
fi

if [ "$1" = "--dev" ]; then
  DIST_FOLDER="$BUILD_NAME-dev"
else
  DIST_FOLDER=$BUILD_NAME
fi

rsync -rc --exclude-from ".distignore" "./" "dist/$DIST_FOLDER"

if [ "$1" = "--dev" ]; then
	cp -f "development.php" "dist/$DIST_FOLDER"
fi

cd dist
zip -r "../artifact/$DIST_FOLDER" "./$DIST_FOLDER/"

echo "BUILD GENERATED: $DIST_FOLDER"
cd -
