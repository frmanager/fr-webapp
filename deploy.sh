#!/bin/bash

export PATH=/usr/local/php71/bin:$PATH
export PATH=/home/$USER/.php/composer:$PATH
export APP_ENV=prod

# Just in Case
git stash

git pull

composer install --no-dev --optimize-autoloader

php bin/console cache:clear --env=prod --no-debug

php bin/console doctrine:schema:update --force

php bin/console app:refresh-donation-db

#FIN
