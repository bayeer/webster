# Webster
Tool for web applications development based on docker-compose containers.


# Installation

1. Clone project
```
# git clone git@github.com:bayeer/webster.git ~/Webster/
```
2. Install packages
```
# cd ~/Webster && composer install
```
3. Create config from sample
```
# cp -p ~/Webster/includes/conf.php.sample ~/Webster/includes/conf.php
```
4. That's all. You're ready to GO!


# Quick start


### Laravel site

setup:
```
# ~/Webster/webster setup-site hellolara.loc --type=laravel
```
delete:
```
# ~/Webster/webster delete-site hellolara.loc
```
commands for docker containers:
```
# ~/Webster/webster restart-docker ~/Dev/php/hellolara/ 
# ~/Webster/webster stop-docker ~/Dev/php/hellolara/ 
# ~/Webster/webster start-docker ~/Dev/php/hellolara/ 
```


### Yii2 site

setup:
```
# ~/Webster/webster setup-site helloyii2.loc --type=yii2 --dbdir=~/DockerDatabases/mysql/
```
delete:
```
# ~/Webster/webster delete-site helloyii2.loc --dbdir=~/DockerDatabases/mysql/helloyii2/
```


### Simple php site (no database)

setup:
```
# ~/Webster/webster setup-site hello.loc
```
delete:
```
# ~/Webster/webster delete-site hello.loc
```


### Bitrix site

setup:
```
# ~/Webster/webster setup-site hellobitrix.loc -t bitrix -d ~/Dev/php/hellobitrix/ -db ~/DockerDatabases/mysql/ -cs cp1251
```
delete:
```
# ~/Webster/webster delete-site hellobitrix.loc -db ~/DockerDatabases/hellobitrix/ 
```


# Advanced usage


### Proxy virtual host to docker container

* Creates reverse proxy config for domain `testsite.loc` to `localhost:3000`:
```
# ~/Webster/webster setup-proxy-vhost testsite.loc localhost:3000
```

* Deletes reverse proxy config for domain `testsite.loc` from `localhost:3000`:
```
# ~/Webster/webster delete-proxy-vhost testsite.loc
```


### Docker commands

* Start docker container (execute `docker-compose up -d`) in project directory:
```
# ~/Webster/webster start-docker ~/Dev/php/hellolara.loc/
```

* Stop docker container (execute `docker-compose down`) in project directory:
```
# ~/Webster/webster stop-docker ~/Dev/php/hellolara.loc/
```

* Restart docker container (execute `docker-compose restart`) in project directory:
```
# ~/Webster/webster restart-docker ~/Dev/php/hellolara.loc/
```


### Delete projects

* Delete site with specific db directory:
```
# ~/Webster/webster delete-site hellolara.loc --dbdir=~/DockerDatabases/mysql/hellolara/
```

* Delete simple (no database) application:
```
# ~/Webster/webster delete-site hello.loc
```

### Other examples

```
# ~/Webster/webster setup-site laratest1.loc --type=laravel --dir=~/Dev/php/laratest1/ --dbdir=~/DockerDatabases/mysql/ --charset=utf8 --distro=yes 
# ~/Webster/webster delete-site laratest1.loc --dir=~/Dev/php/laratest1/ --dbdir=~/DockerDatabases/mysql/laratest1/

# ~/Webster/webster setup-site laratest2.loc --type=laravel --charset=utf8 --distro=yes --proxy=localhost:7001
# ~/Webster/webster setup-site yii2test.loc --type=yii2 --charset=utf8 --distro=yes
# ~/Webster/webster setup-site bxtest.loc --type=bitrix --charset=utf8 --distro=yes
# ~/Webster/webster setup-site mxtest.loc --type=modx --charset=utf8 --distro=yes
# ~/Webster/webster setup-site testapp1.loc --type=simple
# ~/Webster/webster setup-site testapp2.loc --type=yii2adv --dbdir=~/DockerDatabases/mysql/ --distro=no
# ~/Webster/webster setup-site testapp3.loc --type=bitrix --dbdir=~/DockerDatabases/mysql/ --proxy=localhost:7000 --charset=cp1251 --distro=yes

# ~/Webster/webster delete-site testapp3.loc --type=bitrix --dbdir=~/DockerDatabases/mysql/ --proxy=localhost:7000 --charset=cp1251 --distro=yes

# ~/Webster/webster setup-proxy-vhost test.loc localhost:3000
# ~/Webster/webster delete-proxy-vhost test.loc

# ~/Webster/webster restart-docker ~/Dev/php/hellolara/
# ~/Webster/webster stop-docker ~/Dev/php/hellolara/
# ~/Webster/webster start-docker ~/Dev/php/hellolara/
```

# Requirements

* `*nix` operating system required
* nginx installed on your system required
