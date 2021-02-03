#!/bin/bash

# set -xe

export COMPOSE_INTERACTIVE_NO_CLI=1


shutdown=1
install=1
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
    install=1
    init=1
fi



# Start up containers
echo -- docker up
bin/moodle-docker-compose up -d

# Wait for DB to come up (important for oracle/mssql)
echo -- wait for db
bin/moodle-docker-wait-for-db

date

# install PHP Mess detector
if [ "$install" -eq "1" ]; then 
    echo --install PHP mess detector
    docker exec -i moodle-docker_webserver_1 apt-get update
    docker exec -i moodle-docker_webserver_1 apt-get install -y wget
    docker exec -i moodle-docker_webserver_1 wget -c https://phpmd.org/static/latest/phpmd.phar
    docker exec -i moodle-docker_webserver_1 mv phpmd.phar /usr/bin/phpmd
    docker exec -i moodle-docker_webserver_1 chmod +x /usr/bin/phpmd
fi

if [ "$init" -eq "1" ]; then 
    echo -- init phpunit
    docker exec -i moodle-docker_webserver_1 php admin/tool/phpunit/cli/init.php
    echo -- init behat
    docker exec -i moodle-docker_webserver_1 php admin/tool/behat/cli/init.php
fi


