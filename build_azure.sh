#!/bin/bash
set -e #abort when first programm errors

exitWithMessageOnError () {
  if [ ! $? -eq 0 ]; then
    echo "An error has occured during web site deployment."
    echo $1
    exit 1
  fi
}

# Prerequisites
if [ ! -f composer.phar ];
then
    exitWithMessageOnError "You need to commit a 'composer.phar' file in the root of your project to enable composer deployment."
    exit 1
fi

cd /home/site/wwwroot/



php bin/console doctrine:schema:update --force


composer.phar install

php bin/console asset:install --symlink

cp -r /home/site/repository/* ./

