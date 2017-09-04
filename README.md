[![Build Status](https://travis-ci.org/frmanager/fr-webapp.svg?branch=master)](https://travis-ci.org/frmanager/fr-webapp) [![Quality Status](https://sonarcloud.io/api/badges/gate?key=fr-webapp)](https://sonarcloud.io/dashboard?id=fr-webapp)


#LRES PTO Superhero Fun Run Management Website
This Symphony2 Application is used to manage the "LRES PTO" FunRun, which is a school fundraiser where classes and students compete to raise donations for the PTO. This portal handles the daily data ingest, analytics, calculations, and notifications.

The ultimate goal will be for the system to be developed in an "Open Source" spirit where other schools can either leverage the same system, or download and configure the application to use for their own purposes without much customization.


##Setup

This is a SYMFONY2 PHP based application designed to be run on a Dreamhost Shared server and MYSQL server. This is important because it proves that shared hosting (which can sometimes be free for non-profits) can set this up and host it for free.


##Components

- PHP 5.6 or above
- Composer (PHP Package Management)
- Bower (JS Package Management)
- RDBMS (We use MySQL)
- Phing (For CI/CD)
- pear (Net_FTP) (Specific for Dreamhost)


##Configuration


1. Download repository from Github
2. Setup app/config/parameters.yml
3. Install PHP packages via composer


```
composer self-update
composer install
composer update
```


4. install Javascript packages via Bower


```
export BOWERPHP_TOKEN=<GITHUB_API_KEY>;
vendor/beelab/bowerphp/bin/bowerphp install
```

Go [here](https://github.com/settings/tokens) to create a Github API Token. This will fail without one due to call limits on the public api.


## Running Locally


```
php bin/console server:start
```

URL:[localhost:8000](localhost:8000)



##CI/CD (Phing) Configuration
The Phing configuration is dynamic and will support any amount of environments. It requires SSH and FTP access to the server. I'm still in the initial phases, but essentially all you have to do is do a base setup as follows:


1. clone branch to remote location that you want to deploy to. For example, I cloned "test" to my testfunrunfbapp directory but it is test....yeah yeah.....). So I just ran some shell magic to initialize the environment.


```
git clone https://github.com/lrespto/funrun-symfony2.git ./
git checkout dev  # <-- name of the branch I wanted
composer update # <-- for some reason, phing couldn't run this the first time.
composer install # <-- for some reason, phing couldn't run this the first time.
```


2. Setup the build.properties file under "config" folder

```
environments=test,production

test.hostname=hostname for SSH
test.ftphostname=hostname for FTP
test.ftpport=21
test.username=username
test.password=super_secure_password
test.documentroot=testfunrunfbapp.lrespto.org
test.repositoryname=test

githubApiKey=<GITHUB_API_KEY>
```


### .bash_profile on dreamhost

```
umask 002
PS1='[\h]$ '
export PATH=/usr/local/php71/bin:$PATH
export PATH=/home/<USERNAME>/.php/composer:$PATH
export SYMFONY_ENV=prod
export BOWERPHP_TOKEN=<GITHUB_API_KEY>
```


### Update entity getters and setters from database



```
php bin/console doctrine:generate:entities AppBundle:Donation
```



### Update database from entities


```
php bin/console doctrine:schema:update --force
```


### Bootstrap database using AliceBundle

```
php bin/console hautelook:fixtures:load
```

## TODO: TimeZone

Need to set default timezone as date.timezone = "America/New_York"
