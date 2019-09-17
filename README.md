# Webster
Tool for web applications development based on docker-compose containers.


# Installation

1. Clone project
```
> git clone git@github.com:bayeer/webster.git ~/Webster/
```
2. Install packages
```
> cd ~/Webster && composer install
```
3. That's all. You're ready to GO!


# Quick start


### Setup projects

* Laravel site:
```
> ./webster setup-site hellolara.loc --type=laravel --dbdir=~/Dev/DockerDatabases/mysql/
```

* Yii2 site:
```
> ./webster setup-site helloyii2.loc --type=yii2 --dbdir=~/Dev/DockerDatabases/mysql/
```

* Simple php site (no database):
```
> ./webster setup-site hello.loc --dir=/Dev/html/hello/
```

* Bitrix site:
```
> ./webster setup-site hello.loc -t bitrix -d ~/Dev/php/hellobitrix/ -db ~/Dev/DockerDatabases/mysql/
```


### Proxy virtual host to docker container

* Creates reverse proxy config for domain `testsite.loc` to `localhost:3000`:
```
> ./webster setup-proxy-vhost testsite.loc localhost:3000
```

* Deletes reverse proxy config for domain `testsite.loc` from `localhost:3000`:
```
> ./webster delete-proxy-vhost testsite.loc
```

### Docker commands

* Start docker container (execute `docker-compose up -d`) in project directory:
```
> ./webster start-docker ~/Dev/php/hellolara.loc/
```

* Stop docker container (execute `docker-compose down`) in project directory:
```
> ./webster stop-docker ~/Dev/php/hellolara.loc/
```

* Restart docker container (execute `docker-compose restart`) in project directory:
```
> ./webster restart-docker ~/Dev/php/hellolara.loc/
```




### Delete projects

* Delete application with database:
```
> ./webster delete-site hellolara.loc --dbdir=~/Dev/DockerDatabases/mysql/hellolara/
```

* Delete simple (no database) application:
```
> ./webster delete-site hello.loc
```

### Other examples

```
> ./webster setup-site laratest.loc --type=laravel --charset=utf8 --distro=yes
> ./webster setup-site laratest.loc --type=laravel --charset=utf8 --distro=yes --proxy=localhost:7001
> ./webster setup-site yii2test.loc --type=yii2 --charset=utf8 --distro=yes
> ./webster setup-site bxtest.loc --type=bitrix --charset=utf8 --distro=yes
> ./webster setup-site mxtest.loc --type=modx --charset=utf8 --distro=yes
> ./webster setup-site testapp1.loc --type=simple
> ./webster setup-site testapp2.loc --type=yii2adv --dbdir=~/MyDatabasesDir/ --distro=no
> ./webster setup-site testapp3.loc --type=bitrix --dbdir=~/MyDatabasesDir/ --proxy=localhost:7000 --charset=cp1251 --distro=yes

> ./webster setup-proxy-vhost test.loc localhost:3000
> ./webster delete-proxy-vhost test.loc

> ./webster restart-docker ~/Dev/php/hellolara/
> ./webster stop-docker ~/Dev/php/hellolara/
> ./webster start-docker ~/Dev/php/hellolara/
```
