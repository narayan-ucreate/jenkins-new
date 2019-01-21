import groovy.json.JsonSlurper
def username='';
def commit_message =''

pipeline {
    agent any
    environment {
        REDIS_HOST='localhost'
        DB_CONNECTION='pgsql'
        DB_HOST='ec2-13-233-43-203.ap-south-1.compute.amazonaws.com'
        DB_PORT='5432'
        DB_DATABASE='test'
        DB_USERNAME='postgres'
        DB_PASSWORD='postgres'
        REPO_URL='narayan-ucreate/jenkins'
        ACCESS_TOKEN= credentials('JENKINS_ACCESS_TOKEN')
        PROJECT_NAME='ucreate-review-tool'
        ERROR_MESSAGE = ''

    }
    stages {
         stage('Check Rejected Code') {
              steps {
                updateGithubStatus('pending')
                script {
                    username = sh(script: 'git show -s --pretty=%an', returnStdout: true)
                    commit_message =  sh (script: "git log --format=%B -n 1 "+env.GIT_COMMIT, returnStdout: true)
                    def response = sh(script: 'curl https://production-review-tool.herokuapp.com/api/checkReadyToDeploy?app_name='+env.PROJECT_NAME, returnStdout: true)
                    def json = new JsonSlurper().parseText(response)
                    def rejected_count = "${json.rejected_count}"
                    if (rejected_count != '0') { // if rejected commit is more than 1 it will stopped to build process
                        //currentBuild.result = 'FAILURE'
                        //ERROR_MESSAGE = 'Build faild due to rejected commits.'
                        //error "Build faild due to rejected commits."
                        //sh 'exit 1'
                    }
                 }
              }
         }
        stage('Database Setup') {
             steps {
                 sh 'docker-compose -f docker-compose.yml up -d pgsql'
                 sh 'docker-compose -f docker-compose.yml up -d pgadmin'
             }
        }
        stage('Unit Testing') {
             agent {
                 docker { image 'ucreateit/php7.2:v0.1' }
             }
             steps {
                 sh "php -r \"copy('.env.example', '.env');\""
                 sh 'composer install -n --prefer-dist'
                 sh 'php artisan key:generate'
                 sh './vendor/bin/phpunit'
             }
        }
    }
    post {
        success {
             updateGithubStatus('success')
             notifyToSlack('success', username, commit_message)

        }
        failure {
             notifyToSlack('failed', username, commit_message)
             updateGithubStatus('failure')
        }
    }
}

void updateGithubStatus(status) {
     sh 'curl https://api.github.com/repos/narayan-ucreate/jenkins/statuses/' + env.git_COMMIT + '?access_token=' + env.ACCESS_TOKEN + ' --header "Content-Type: application/json" --data "{\\"state\\": \\"' + status + '\\", \\"description\\": \\"Jenkins\\"}"'
}

void notifyToSlack(status, username, commit_message)
{
    sh 'curl https://production-review-tool.herokuapp.com/api/buildNotification --header "Content-Type: application/json" --request POST --data "{\\"payload\\" : {\\"build_parameters\\": {\\"CIRCLE_JOB\\" : \\"uat-push\\"}, \\"build_url\\" : \\"asdf\\", \\"committer_name\\" : \\"' + username + '\\", \\"status\\" : \\"' + status + '\\", \\"subject\\" : \\"' + commit_message + '\\", \\"reponame\\" : \\"'+env.PROJECT_NAME+'\\", \\"outcome\\" :\\"'+status+'\\",\\"branch\\" : \\"master\\"}}"'
}

void deployToHeroku() {
sh 'git push --force https://heroku:$HEROKU_API_KEY@git.heroku.com/$HEROKU_APP_NAME.git HEAD:refs/heads/master'
}

