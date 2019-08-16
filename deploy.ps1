param(
   [string] $environment
)

Write-Host "FR Manager Application Deploy Script"

Write-Host 'Environment:'
Write-Host $environment

if ($environment -eq "dev" ){
    Write-Host "Environment set to dev"

    Write-Host "Grabbing .env and swiftmailer config files" 
    Copy-Item ../config/dev.env ./.env
    Copy-Item ../config/dev.swiftmailer.yaml ./config/packages/swiftmailer.yaml
}
elseif ($environment -eq "test") {
    Write-Host "Environment set to test"

    Write-Host "Setting PHP Paths to support Dreamhost Shared"
    $Env:PATH=/usr/local/php72/bin:$PATH
    $Env:PATH=/home/$USER/.php/composer:$PATH

    Write-Host "Grabbing latest from Repo and stashing changes"    
    git stash
    git pull

    Write-Host "Grabbing .env and swiftmailer config files"  
    Copy-Item ../config/test.env ./.env
    Copy-Item ../config/prod.swiftmailer.yaml ./config/packages/swiftmailer.yaml       
}
elseif ($environment -eq "prod") {
    Write-Host "Environment set to prod"

    Write-Host "Setting PHP Paths to support Dreamhost Shared"
    $Env:PATH=/usr/local/php71/bin:$PATH
    $Env:PATH=/home/$USER/.php/composer:$PATH

    Write-Host "Grabbing latest from Repo and stashing changes"    
    git stash
    git pull

    Write-Host "Grabbing .env and swiftmailer config files"  
    Copy-Item ../config/prod.env ./.env
    Copy-Item ../config/prod.swiftmailer.yaml ./config/packages/swiftmailer.yaml
}else{
    Write-Error 'Must identify "dev", "test", or "prod" environment' -ErrorAction Stop
}

Write-Host "Setting Environment Variables"

Get-Content .\.env | Foreach-Object{
    if ($var -ne $null){
        if ($var.SubString(0,1) -ne "#"){
            Write-Host $var
            $var = $_.Split('=')
            New-Variable -Name $var[0] -Value $var[1]
        } 
    }
 }
 
$Env:APP_ENV=${APP_ENV}
$Env:MAIN_APP_URL=${MAIN_APP_URL}
$Env:DATABASE_URL=${DATABASE_URL}
$Env:MAILER_URL=${MAILER_URL}
$Env:MAILER_USERNAME=${MAILER_USERNAME}
$Env:MAILER_PASSWORD=${MAILER_PASSWORD}
$Env:MAILER_HOST=${MAILER_HOST}
$Env:GOOGLE_CLIENT_ID=${GOOGLE_CLIENT_ID}
$Env:GOOGLE_CLIENT_SECRET=${GOOGLE_CLIENT_SECRET}
$Env:MAIN_APP_URL=${MAIN_APP_URL}
$Env:ADMIN_APP_URL=${ADMIN_APP_URL}
$Env:SONARCLOUD_KEY=${SONARCLOUD_KEY}
$Env:PAYPAL_REST_CLIENT_ID=${PAYPAL_REST_CLIENT_ID}
$Env:PAYPAL_REST_CLIENT_SECRET=${PAYPAL_REST_CLIENT_SECRET}
$Env:FEATURE_CREDIT_CARD=${FEATURE_CREDIT_CARD}


if ($environment -eq "dev" ){
    Write-Host "Dev Composer/Symfony Install/Updates"  
    composer install
    php bin/console cache:clear
}
elseif ($environment -eq "test") {
    Write-Host " Production Composer/Symfony Install/Updates"  
    composer install --no-dev --optimize-autoloader
    php bin/console cache:clear --env=prod --no-debug       
}
elseif ($environment -eq "prod") {
    Write-Host " Production Composer/Symfony Install/Updates"  
    composer install --no-dev --optimize-autoloader
    php bin/console cache:clear --env=prod --no-debug
}


Write-Host "Syncronizing Database and refreshing donation database"  
php bin/console doctrine:schema:update --force

### NOTE: TEMPORARILY REMOVED AS COMMANDS WORK DIFFERENTLY IN SYMFONY4
#php bin/console app:refresh-donation-db

#FIN