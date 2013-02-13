#!/bin/bash

if ([ $# -ne 1 ])
then
  echo "Missing version argument, sample usage: ./archive.sh <version>"
else
  pushd `mktemp -d -t clientlib`
  # clone repository
  hg clone https://@code.google.com/p/gshoppingcontent-php/
  # remove mercurial
  rm -rf ./gshoppingcontent-php/.hg

  # archive the repo
  tar -zcf "gshoppingcontent-php-"$1".tar.gz" ./gshoppingcontent-php
  zip -r "gshoppingcontent-php-"$1".zip" ./gshoppingcontent-php

  popd
fi
