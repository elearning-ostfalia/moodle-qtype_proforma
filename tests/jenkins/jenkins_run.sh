#!/bin/bash

# set -xe


phpmd=1
behat=1
phpunit=1

date

# Php Mess detector
if [ "$phpmd" -eq "1" ]; then 
    echo -- PHP Mess detector
    docker exec -i moodle-docker_webserver_1  phpmd --exclude "tests/*" question/type/proforma text phpmd.xml
fi


# PhpUnit
if [ "$phpunit" -eq "1" ]; then 
    echo -- run phpunit
    docker exec -i moodle-docker_webserver_1 vendor/bin/phpunit --configuration question/type/proforma/tests/phpunit.xml
fi
date


# Behat
if [ "$behat" -eq "1" ]; then 
    # All tests
    echo -- run behat
    docker exec -i moodle-docker_webserver_1 vendor/bin/behat --config /var/www/behatdata/behatrun/behat/behat.yml --tags '@qtype_proforma'
fi


date

