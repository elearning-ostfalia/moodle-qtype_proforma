#!/bin/bash

# set -xe

export COMPOSE_INTERACTIVE_NO_CLI=1


shutdown=0
install=0
init=1

# Change working directory
cd $WORKSPACE/moodle-docker


# Set up path to Moodle code
export MOODLE_DOCKER_WWWROOT=$WORKSPACE/moodle
# Choose a db server (Currently supported: pgsql, mariadb, mysql, mssql, oracle)
export MOODLE_DOCKER_DB=$DATABASE
#export MOODLE_DOCKER_DB=pgsql

# export MOODLE_DOCKER_SELENIUM_VNC_PORT=5900

export MOODLE_DOCKER_PHP_VERSION=$PHP

# chrome is faster than firefox
export MOODLE_DOCKER_BROWSER=chrome

echo "PHP is $MOODLE_DOCKER_PHP_VERSION"
echo "DATABASE is $MOODLE_DOCKER_DB"

# Ensure customized config.php for the Docker containers is in place
echo -- copy config.php
cp config.docker-template.php $MOODLE_DOCKER_WWWROOT/config.php

if [ "$shutdown" -eq "1" ]; then 
    echo -- docker down
    bin/moodle-docker-compose down || true
    # Build Images
    echo -- build docker
    bin/moodle-docker-compose "build"
    install=0
    init=1
fi



# Start up containers
date
echo -- docker up
bin/moodle-docker-compose up -d

# Wait for DB to come up (important for oracle/mssql)
echo -- wait for db
bin/moodle-docker-wait-for-db

date

docker ps

# create and start praktomat, attach to moodle network
# docker-compose -f docker-compose-test.yml build
# docker-compose -f docker-compose-test.yml up

if [ "$install" -eq "1" ]; then 
    bin/moodle-docker-compose exec webserver apt-get update
    # install Stylelint
    # docker exec -i moodle-docker_webserver_1 apt install -y nodejs
    # docker exec -i moodle-docker_webserver_1 apt install -y npm    
    # docker exec -i moodle-docker_webserver_1 npm install --save-dev stylelint stylelint-config-standard
    
    
    # install PHP Mess detector
    echo --install PHP mess detector
    # todo: use bin/moodle-docker-compose exec webserver 
    bin/moodle-docker-compose exec webserver apt-get install -y wget
    bin/moodle-docker-compose exec webserver wget -c https://phpmd.org/static/latest/phpmd.phar
    bin/moodle-docker-compose exec webserver mv phpmd.phar /usr/bin/phpmd
    bin/moodle-docker-compose exec webserver chmod +x /usr/bin/phpmd
    
fi

if [ "$init" -eq "1" ]; then 
    echo -- init phpunit
    bin/moodle-docker-compose exec webserver php admin/tool/phpunit/cli/init.php
    echo -- init behat
    bin/moodle-docker-compose exec webserver php admin/tool/behat/cli/init.php
fi


