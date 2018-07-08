#!groovy

dockerTagApp = 'weegy/siwecos-core-api:master'

def checkoutAndInstall() {
        checkout scm

        sh 'php7.1 $(which composer) install'
        sh 'yarn install'
}

node('php7.1&&node8.x') {
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