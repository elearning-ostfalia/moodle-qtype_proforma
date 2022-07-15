// unfortunately jenkins matrix steps runs in parallel which leads to problems
// with Moodle HQ docker. So we use a script based implementation.

// Not all combinations shall be tested because this takes too much time.
def combinations = [
    ['311', '7.4', 'mysql'],
    ['311', '7.3', 'pgsql'],
    ['400', '8.0', 'mysql'],
    ['master', '8.0', 'pgsql']
];

pipeline {
    agent { node('WSL') } // run in WSL 2 environment on Windows with Ubuntu 20 installed.
    parameters {
        choice(name: 'SOURCE_ORIGIN', choices: ['github', 'local'], description: 'Where to get source code from')
        booleanParam(name: 'DO_NOT_FETCH_MOODLE', defaultValue: true, description: 'Use Moodle code from disk')
        choice(name: 'MOODLE_VERSION', choices: ['all', '311', '400', 'master'], description: 'Run with specific Moodle version')
        choice(name: 'DATABASE_TYPE', choices: ['all', 'mysql', 'pgsql'], description: 'Run with specific database')
        choice(name: 'PHP_VERSION', choices: ['all', '7.3', '7.4', '8.0'], description: 'Run with specific PHP version')
    }
    stages {
        stage('init') {
            steps {      
                script{
                    // if all configured combinations shall be run:
                    if (params.MOODLE_VERSION == "all") {
                        echo "run ALL combinations"
                        // use predefined combinations
                        for (combination in combinations) {
                            stage("Moodle " + combination[0] + " PHP " + combination[1] + " " + combination[2]){
                                try {
                                    echo "run test catching exceptions"
                                    runTest(params.DO_NOT_FETCH_MOODLE, params.SOURCE_ORIGIN, combination[0], combination[1], combination[2])
                                } catch (Exception err) {
                                    currentBuild.result = 'SUCCESS'
                                    // change visualisation of stage to yellow
                                    unstable('STEP FAILED')
                                }
                            } // stage
                        } // for
                    } else {
                        echo "run SPECIFIC combination"
                        // use specific versions
                        stage("Moodle ${params.MOODLE_VERSION} PHP_VERSION ${params.PHP_VERSION} ${params.DATABASE_TYPE}"){
                            runTest(params.DO_NOT_FETCH_MOODLE, params.SOURCE_ORIGIN, params.MOODLE_VERSION, params.PHP_VERSION, params.DATABASE_TYPE)
                        }                    
                    }
                }
            }
        }
    }
}

def runTest(boolean DO_NOT_FETCH_MOODLE, String source_origin, String moodle_version, String php_version, String db_type) {
    // set environment variables for shell scripts
    env.PHP = php_version
    env.DATABASE = db_type
    // local path to source code
    proforma_path="/mnt/e/users/karin/job/ostfalia/git/moodle/"
    
    echo "use source code from " + source_origin
    echo "with PHP " + php_version + " and DB " + db_type
   
    echo '** Getting praktomat from github...'           
    dir('praktomat') {
        git url: 'https://github.com/elearning-ostfalia/Proforma-Praktomat.git'
			// create .env file with credentials
            sh('cp .env.example .env')
			sh('wget https://download.randoom.org/setlX/pc/setlX_v2-7-2.binary_only.zip')
			// Note that unzip must exist (otherwise install previously)
			sh('unzip -p setlX_v2-7-2.binary_only.zip setlX.jar > extra/setlX-2.7.jar')
            sh('docker-compose down')
            sh('docker-compose build')
			// TODO Problem: 
			// Moodle-Network must exist before starting Praktomat...
            sh('docker-compose -f docker-compose-test.yml -f docker-compose.yml up -d')
    }      
    
    if (DO_NOT_FETCH_MOODLE) {
        echo "skip checkout Moodle, use current version from disk"
    } else {
        echo "checkout Moodle " + moodle_version    
        echo '** Getting moodle-docker from github...'           
        dir('moodle-docker') {
            git url: 'https://github.com/moodlehq/moodle-docker.git'
        }    
        echo '** Getting moodle from github...'           
        dir('moodle') {
            if (moodle_version == "master") {
                branch = moodle_version
            } else {
                branch = "MOODLE_" + moodle_version + "_STABLE"
            }
            git branch: branch,
                url: 'https://github.com/moodle/moodle.git'
        }
    }
    
    if (source_origin == 'github') {
        echo '** Getting ProFormA code from github...'        
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
            // echo '** Initialising test...'
            // sh('./jenkins_init.sh')
            echo '** Running tests...'
            sh('./jenkins_run.sh')
        }        
    } else {    
        echo '** Copying ProFormA code from local disk...' + proforma_path       
        dir('moodle') {        
            sh('rsync -rv --delete-before -q --exclude=.git ' + proforma_path + 'question/behaviour/adaptiveexternalgrading question/behaviour')
            sh('rsync -rv --delete-before -q --exclude=.git ' + proforma_path + 'question/type/proforma question/type')
            sh('rsync -rv --delete-before -q --exclude=.git ' + proforma_path + 'question/format/proforma question/format')
            // Copy scripts to home directory in order to avoid relative paths
            // (and starting from outside WSL)
            sh('cp ' + proforma_path + 'question/type/proforma/tests/jenkins/jenkins_init.sh /home/jenkins')
            sh('cp ' + proforma_path + 'question/type/proforma/tests/jenkins/jenkins_run.sh /home/jenkins')
        }            
        echo '** Initialising test...'
        sh('/home/jenkins/jenkins_init.sh')
        echo '** Starting tests...'
        sh('/home/jenkins/jenkins_run.sh')    
    }
}