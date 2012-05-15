DEPRECATED
===========

Usar o README do projeto


Coderockr SOA Server
=========================

The goal of this project is to enable easy access of entities using Rest and PHP scripts using RPC

This application depends on these projects:

- [Silex](http://silex.sensiolabs.org/)
- [DMS/Filter](https://github.com/rdohms/DMS-Filter)
- [Doctrine](http://www.doctrine-project.org/) 
- [Symfony ClassLoader](https://github.com/symfony/ClassLoader)
- [Symfony Validator](https://github.com/symfony/Validator.git)

Instalation
----------

- Clone this project
- Execute vendors.sh to install dependencies (Linux and Mac for now)
- Create a virtual domain in Apache

---
	<VirtualHost *:80>
        DocumentRoot "/Users/eminetto/Documents/Projects/SOA-Server"
		ServerName soa.dev
		<Directory "/Users/eminetto/Documents/Projects/SOA-Server">
			Options Indexes Multiviews FollowSymLinks
			AllowOverride All
			Order allow,deny
			Allow from all

            <Limit GET HEAD POST PUT DELETE OPTIONS>
                    Order Allow,Deny
                    Allow from all
                </Limit>
            
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule !\.(js|ico|gif|jpg|png|css|htm|html|txt|mp3)$ index.php
        RewriteRule .? - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
        </Directory>
    </VirtualHost>

- Configure /etc/hosts:
    
    127.0.0.1   soa.dev


Rest
----

To be avaiable as a Rest service an entity must extend the model\Entity class. As an exemple, there is an User entity in this repository. To use it you must create the table, as the SQL below. The database configuration is in configs/configs.php file.

    CREATE TABLE `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `login` varchar(10) DEFAULT NULL,
      `password` varchar(10) DEFAULT NULL,
      PRIMARY KEY (`id`)
    );

RPC
---

To be avaiable as a Rpc service a class must extend service\Service class, as shown in the service\Login.php sample.

Authorization
-----------

The service expects an value to Authorization in the request header. The Authorization header will be validated with configs/clients.php contents. 

How to access
------------

Urls:

	http://soa.dev/user/1 - show user with id = 1
	http://soa.dev/users - show all users
	http://soa.dev/sample - the sample.html file shows how to use the services using Javascript
	
	
#Geração das tabelas

Para que o Doctrine gere as tabelas no banco de dados baseado nas entidades:

	cd Backend/SOA-Server
	APPLICATION_ENV=development php ./bin/doctrine.php orm:schema-tool:create
	APPLICATION_ENV=testing php ./bin/doctrine.php orm:schema-tool:create
	
Desta forma são criadas as tabelas na base de desenvolvimento e na de testes
	