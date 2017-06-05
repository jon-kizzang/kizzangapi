
Kizzang API Producton
==

Introduction
==
This project provide a lot of game's api.
This is production branch, so it should contains the stable code.

Code structure
==
**www** - this folder contains code of codeigniter

===
Install
==

This project using Chef solo to manager softwares and deploy application


## I. On development environment
### 1. Vagrant
`Vagrant` is tool allow developer create a Vitual machine for development.
Please download at [vagrant home page](http://www.vagrantup.com) 

Below is some tools which would installed on `Vagrant` box.

1. [Apache 2](http://www.apache.org/)
2. [MySql](http://www.mysql.com/)
3. [PHP5](http://php.net/)
4. [PHP composer](https://getcomposer.org/)

**Setup**
	cd /Development/kizzangChef
	vagrant up --provision
**Run on browser** [dev-api.kizzang.com](dev-api.kizzang.com)

