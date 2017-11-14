# SIWECOS-Core-API

### Laradock
To start development in a docker environment just switch to the `laradock` folder and run:

```
docker-compose up -d nginx mysql
```

If you want to use the `artisan` commands or composer, just use the workspace container:

```
docker-compose exec --user laradock workspace bash
```

Further information at the [Laradock Project Website](https://github.com/laradock/laradock)
