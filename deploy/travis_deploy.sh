#!/bin/bash
# travis_deploy.sh $TRAVIS_BUILD_DIR
TRAVIS_BUILD_DIR=$1
if [ -z "$TRAVIS_BUILD_DIR" ]; then
  echo "TRAVIS_BUILD_DIR is Empty $TRAVIS_BUILD_DIR"
  exit 1
else
  echo "Vars are OK, let's go"
fi
if rsync -r --delete-after --quiet $TRAVIS_BUILD_DIR automate@back1.igotta.beer:/var/www/geekdpt.io; then
  echo "rsync OK"
else
  echo "rsync failed"
  exit 1
fi
if ssh automate@back1.igotta.beer sudo supervisorctl restart geekdpt.io; then
  echo "Server launched, mission accomplished commander"
  exit 0
else
  echo "Server could not be started, mission failed commander"
  exit 1
fi
