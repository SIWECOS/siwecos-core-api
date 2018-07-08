#!groovy

dockerTagApp = 'weegyman/siwecos-core-api'

def checkoutAndInstall() {
        checkout scm
        sh 'sudo apt-get install -y python-software-properties'
        sh 'sudo add-apt-repository -y ppa:ondrej/php'
        sh 'sudo apt-get update -y'
        sh 'sudo apt-get install nodejs -y'
        sh 'sudo apt-get install curl php-cli php-mbstring php7.2-xml php7.2-dom git unzip -y'
        sh 'cd ~'
        sh 'curl -sS https://getcomposer.org/installer -o composer-setup.php'
        sh 'sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer'
        sh 'composer install'
}

node ('docker') {
    ws {
        stage('prepare') {
            checkoutAndInstall()
        }

        stage('docker-build') {
            parallel(
                app: {
                    sh "docker build -t $dockerTagApp ."
                }
            )
        }

        stage('docker-push'){
            steps{
                withDockerRegistry([credentialsId: 'https://registry.hub.docker.com', url: '']) {
                    sh 'docker push ${doockerTagApp}:latest'
                }
            }
        }
    }
}