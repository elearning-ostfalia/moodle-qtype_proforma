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
        choice(name: 'SOURCE_ORIGIN', choices: ['github', 'local'], description: 'Where to get source code from')
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
                                try {
                                    echo "run test catching exceptions"
                                    runTest(params.SOURCE_ORIGIN, combination[0], combination[1], combination[2])
                                } catch (Exception err) {
                                    currentBuild.result = 'SUCCESS'
                                    // change visualisation of stage to yellow
                                    unstable('STEP FAILED')
                                }
                            } // stage
                        } // for
                    } else {
                        echo "run specific combination"
                        // use specific versions
                        stage("Moodle ${params.MOODLE_VERSION} PHP_VERSION ${params.PHP} ${params.DATABASE_TYPE}"){
                            runTest(params.SOURCE_ORIGIN, params.MOODLE_VERSION, params.PHP_VERSION, params.DATABASE_TYPE)
                        }                    
                    }
                }
            }
        }
    }
}

def runTest(String source_origin, String moodle_version, String php_version, String db_type) {
    // set environment variables for shell scripts
    env.PHP = php_version
    env.DATABASE = db_type
    
    echo "Source from " + source_origin
    echo "Checkout Moodle " + moodle_version
    echo "Use PHP " + php_version + " and DB " + db_type
    
    proforma_path="/mnt/e/users/karin/job/ostfalia/git/moodle/"
    
    dir('moodle-docker') {
        git url: 'https://github.com/moodlehq/moodle-docker.git'
    }    
    dir('moodle') {
        if (moodle_version == "master") {
            branch = moodle_version
        } else {
            branch = "MOODLE_" + moodle_version + "_STABLE"
        }
        git branch: branch,
            url: 'https://github.com/moodle/moodle.git'
    }
    if (source_origin == 'github') {
        echo 'Getting ProFormA code from github...'        
        dir('moodle/question/type/proforma') {
            git branch: 'master',
                url: 'https://github.com/elearning-ostfalia/moodle-qtype_proforma.git'
        }
        dir('moodle/question/format/proforma') {
            git url: 'https://github.com/elearning-ostfalia/moodle-qformat_proforma.git'
        }
        dir('moodle/question/behaviour/adaptiveexternalgrading') {
            git url: 'https://github.com/elearning-ostfalia/moodle-qbehaviour_adaptiveexternalgrading.git'
        }
        dir('moodle/question/type/proforma/tests/jenkins') {
            echo 'Initialising test...'
            sh('./jenkins_init.sh')
            echo 'Starting tests...'
            sh('./jenkins_run.sh')
        }        
    } else {
        echo 'Copying ProFormA code from local disk...'        
        dir('moodle') {        
            sh('rsync -rv --delete-before -q --exclude=.git ' + proforma_path + 'question/behaviour/adaptiveexternalgrading question/behaviour')
            sh('rsync -rv --delete-before -q --exclude=.git ' + proforma_path + 'question/type/proforma question/type')
            sh('rsync -rv --delete-before -q --exclude=.git ' + proforma_path + 'question/format/proforma question/format')
            // Copy scripts to home directory in order to avoid relative paths
            // (and starting from outside WSL)
            sh('cp ' + proforma_path + 'question/type/proforma/tests/jenkins/jenkins_init.sh /home/jenkins')
            sh('cp ' + proforma_path + 'question/type/proforma/tests/jenkins/jenkins_run.sh /home/jenkins')
        }            
        echo 'Initialising test...'
        sh('/home/jenkins/jenkins_init.sh')
        echo 'Starting tests...'
        sh('/home/jenkins/jenkins_run.sh')    
    }
}