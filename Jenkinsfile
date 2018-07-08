#!groovy

dockerTagApp = 'siwecos/siwecos-core-api:master'

def checkoutAndInstall() {
        checkout scm

        sh 'composer install'
        sh 'yarn install'
}

node {
    ws {
        stage('prepare') {
            checkoutAndInstall()
        }

        stage('test') {
            sh 'php7.1 vendor/bin/phpunit -c phpunit.xml'
            junit allowEmptyResults: true, testResults: 'build/logs/junit.xml'
        }

        stage('docker-build') {
            parallel(
                app: {
                    sh "docker build -f app.dockerfile -t $dockerTagApp ."
                }
            )
        }
    }
}