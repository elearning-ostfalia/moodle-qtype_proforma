// unfortunately jenkins matrix steps runs in parallel which leads to problems
// with Moodle HQ docker. So we use a script based implementation.

// Not all combinations shall be tested because this takes too much time.
// 3.8 requires at least PHP 7.1-7.4 (since 3.8.3)
// 3.9, 3.10: 7.2-7.4
def combinations = [
    ['38', '7.1', 'mysql'],
    ['38', '7.2', 'pgsql'],
    ['39', '7.3', 'mysql'],
    ['310', '7.4', 'pgsql'],
    ['311', '7.4', 'pgsql'],
    ['master', '7.4', 'pgsql']
];

pipeline {
    agent { node('WSL') } // run in WSL 2 environment on Windows with Ubuntu 20 installed.
    parameters {
        choice(name: 'MOODLE_VERSION', choices: ['all', '38', '39', '310', '311', 'master'], description: 'Run with specific Moodle version')
        choice(name: 'DATABASE_TYPE', choices: ['all', 'mysql', 'pgsql'], description: 'Run with specific database')
        choice(name: 'PHP_VERSION', choices: ['all', '7.1', '7.2', '7.3', '7.4'], description: 'Run with specific PHP version')
    }
    stages {
        stage('init') {
            steps {      
                script{
                    // if all configured combinations shall be run:
                    if (params.MOODLE_VERSION == "all") {
                        echo "RUN ALL COMBINATIONS"
                        // use predefined combinations
                        for (combination in combinations) {
                            stage("Moodle " + combination[0] + " PHP " + combination[1] + " " + combination[2]){
                                runTest(combination[0], combination[1], combination[2])
                            }                    
                        }
                    } else {
                        echo "run specific combination"
                        // use specific versions
                        stage("Moodle ${params.MOODLE_VERSION} PHP_VERSION ${params.PHP} ${params.DATABASE_TYPE}"){
                            runTest(params.MOODLE_VERSION, params.PHP_VERSION, params.DATABASE_TYPE)
                        }                    
                    }
                }
            }
        }
    }
}

def runTest(String moodle_version, String php_version, String db_type) {
    // set environment variables for shell scripts
    env.PHP = php_version
    env.DATABASE = db_type
    
    echo "Checkout Moodle " + moodle_version
    echo "Use PHP " + php_version + " and DB " + db_type
    
    dir('moodle') {
        if (moodle_version == "master") {
            branch = moodle_version
        } else {
            branch = "MOODLE_" + moodle_version + "_STABLE"
        }
        git branch: branch,
            url: 'https://github.com/moodle/moodle.git'
    }
    dir('moodle/question/type/proforma') {
        git url: 'https://github.com/elearning-ostfalia/moodle-qtype_proforma.git'
    }
    dir('moodle/question/format/proforma') {
        git url: 'https://github.com/elearning-ostfalia/moodle-qformat_proforma.git'
    }
    dir('moodle/question/behaviour/adaptiveexternalgrading') {
        git url: 'https://github.com/elearning-ostfalia/moodle-qbehaviour_adaptiveexternalgrading.git'
    }
    dir('moodle-docker') {
        git url: 'https://github.com/moodlehq/moodle-docker.git'
    }
    dir('moodle/question/type/proforma/tests/jenkins') {
        echo 'Initialising test...'
        sh('./jenkins_init.sh')
        echo 'Starting tests...'
        sh('./jenkins_run.sh')
    }
    /* echo 'Initialising test...'
    sh('/home/jenkins/jenkins_init.sh')
    echo 'Starting tests...'
    sh('/home/jenkins/jenkins_run.sh')    
    */
}