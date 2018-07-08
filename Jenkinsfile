#!groovy

dockerTagApp = 'siwecos/siwecos-core-api:master'

def checkoutAndInstall() {
        checkout scm
        sh 'sudo apt-get install -y python-software-properties'
        sh 'sudo add-apt-repository -y ppa:ondrej/php'
        sh 'sudo apt-get update -y'
        sh 'sudo apt-get install nodejs -y'
        sh 'sudo apt-get install yarn -y'
        sh 'sudo apt-get install php7.1 php7.1-json -y'
        sh 'sudo apt-get install curl php-cli php-mbstring git unzip -y'
        sh 'cd ~'
        sh 'curl -sS https://getcomposer.org/installer -o composer-setup.php'
        sh 'sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer'
        sh 'composer install'
        sh 'yarn install'
}

node ('docker') {
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