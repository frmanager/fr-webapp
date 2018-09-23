#!/bin/bash
echo "FR Manager Application Deploy Script"
source .env



if [ "$1" = "dev" ]
then
    echo "Environment set to dev"

    echo "Grabbing .env and swiftmailer config files" 
    cp ../config/dev.env ./.env
    cp ../config/dev.swiftmailer.yaml ./config/packages/swiftmailer.yaml
elif [ "$1" = "prod" ]
then
    echo "Environment set to prod"

    echo "Setting PHP Paths to support Dreamhost Shared"
    export PATH=/usr/local/php71/bin:$PATH
    export PATH=/home/$USER/.php/composer:$PATH

    echo "Grabbing latest from Repo and stashing changes"    
    git stash
    git pull

    echo "Grabbing .env and swiftmailer config files"  
    cp ../config/prod.env ./
    cp ../config/prod.swiftmailer.yaml ./config/packages/swiftmailer.yaml
else
    echo 'Must identify "dev" or "prod" environment'
    exit 1 # terminate and indicate error
fi


echo "Setting Environment Variables"
export APP_ENV=${APP_ENV}
export MAIN_APP_URL=${MAIN_APP_URL}
export DATABASE_URL=${DATABASE_URL}
export MAILER_URL=${MAILER_URL}
export MAILER_USERNAME=${MAILER_USERNAME}
export MAILER_PASSWORD=${MAILER_PASSWORD}
export MAILER_HOST=${MAILER_HOST}
export GOOGLE_CLIENT_ID=${GOOGLE_CLIENT_ID}
export GOOGLE_CLIENT_SECRET=${GOOGLE_CLIENT_SECRET}
export MAIN_APP_URL=${MAIN_APP_URL}
export ADMIN_APP_URL=${ADMIN_APP_URL}
export SONARCLOUD_KEY=${SONARCLOUD_KEY}
export PAYPAL_REST_CLIENT_ID=${PAYPAL_REST_CLIENT_ID}
export PAYPAL_REST_CLIENT_SECRET=${PAYPAL_REST_CLIENT_SECRET}
export FEATURE_CREDIT_CARD=${FEATURE_CREDIT_CARD}



if [ "$1" = "dev" ]
then
    echo "Dev Composer/Symfony Install/Updates"  
    composer install
    php bin/console cache:clear
elif [ "$1" = "prod" ]
then
    echo " Production Composer/Symfony Install/Updates"  
    composer install --no-dev --optimize-autoloader
    php bin/console cache:clear --env=prod --no-debug
fi



echo "Syncronizing Database and refreshing donation database"  
php bin/console doctrine:schema:update --force

### NOTE: TEMPORARILY REMOVED AS COMMANDS WORK DIFFERENTLY IN SYMFONY4
#php bin/console app:refresh-donation-db

#FIN
