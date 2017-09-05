#!/bin/bash

export PATH=/usr/local/php71/bin:$PATH
export PATH=/home/$USER/.php/composer:$PATH
export SYMFONY_ENV=prod

# Just in Case
git stash

git pull

composer install --no-dev --optimize-autoloader

php bin/console cache:clear --env=prod --no-debug

php bin/console doctrine:schema:update --force


#FIN
