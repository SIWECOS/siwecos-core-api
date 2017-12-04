# SIWECOS-Core-API

[![Build Status](https://travis-ci.org/SIWECOS/siwecos-core-api.svg?branch=master)](https://travis-ci.org/SIWECOS/siwecos-core-api)

### Vagrant / Valet

For usage with a mac, we recommend the usage of Valet, a leightweight Vagrant wrapper for Laravel
[Laravel Valet](https://github.com/laravel/valet)

#### Installation of Valet

- Install or update Homebrew
```
$ /usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"`
```
```
$ bre update
```
- Install PHP 7.1 and mySql via Homebrew
```
$ brew install homebrew/php/php71
$ brew install mysql
```
- Install Composer (PHP Package manager) [Composer Installation Manual](https://getcomposer.org/doc/00-intro.md)
- Install Valet as global Composer Requirement
```
$ composer global require laravel/valet
$ valet start
```

#### Checkout and bringing coreApi live
- Checkout the GitRepository and build the requirements
```
$ git checkout https://github.com/SIWECOS/siwecos-core-api.git
$ cd siwecos-core-api/CoreApi
$ composer install
```
- Create Environment file
```
$ cp .env.example .env
```
- Create Laravel Appkey
```
$ php artisan key:generate
```
- Modify the .env file to your needs
```
$ nano .env
```
```
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:gEElyp0rOR1LF8PfEjqfB7BO5VkfdyAQQ3v+HlPUrjA=
APP_DEBUG=true
APP_LOG_LEVEL=debug
APP_URL=http://coreapi.dev

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siwecos_core_api
DB_USERNAME=siwecos
DB_PASSWORD=siwecos
```
- Link Valet to Laravel Application
```
$ valet link
```
- Call coreapi.dev in your Browser


#### Swagger npm Package
[Github Page swagger npm package](https://www.npmjs.com/package/swagger)

just start from /Documentation

```
swagger project edit
```

### Laradock
To start development in a docker environment just switch to the `laradock` folder and run:

```
docker-compose up -d caddy mariadb
```

If you want to use the `artisan` commands or composer, just use the workspace container:

```
docker-compose exec --user laradock workspace bash
```

Further information at the [Laradock Project Website](https://github.com/laradock/laradock)

### Swagger
With the custom laradock caddy server you can easily test the API via swagger.

Just set `host: localhost` and `basePath: /api/v1`.

