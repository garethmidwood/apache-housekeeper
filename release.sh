#!/bin/bash

# credit to: https://moquet.net/blog/distributing-php-cli/ 

set -e

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi

TAG=$1
PROJECTNAME=ahousekeeper

#
# Tag & build master branch
#
git checkout master
git push
git tag ${TAG}

box build
shasum docs/downloads/${PROJECTNAME}.phar > docs/downloads/${PROJECTNAME}.version

git add docs/downloads

#
# Commit and push
#
git commit -m "Add version ${TAG}"

git push
git push --tags
echo "New version created."

