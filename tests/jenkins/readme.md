# Jenkins

For integration testing this folder provides some files for use with the Continous Integration tool Jenkins. 

This file describes how to set up the environemt. 
The base of testing is the Moodle docker configuraion on https://github.com/moodlehq/moodle-docker. 

## Requirements

* Jenkins installation on client e.g. Windows (https://www.jenkins.io/)

* Ubuntu linux server with
    - docker (https://docs.docker.com/engine/install/) and docker-compose (https://docs.docker.com/compose/install/)
    - Java
    - a user `jenkins`
    - Jenkins agent started by user `jenkins`

## Jenkins configuration

### client

create a new Pipeline project with pipeline file `jenkinsfile.groovy` copied into the pipeline script editor 








