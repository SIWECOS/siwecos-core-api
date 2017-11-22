# SIWECOS-Core-API

### Vagrant / Valet

For usage with a mac, we recommend the usage of Valet, a leightweight Vagrant wrapper for Laravel
[Laravel Valet](https://github.com/laravel/valet)


### Swagger npm Package
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
