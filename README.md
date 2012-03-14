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
		ServerName soa.local
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
    
    127.0.0.1   soa.local


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

To be avaiable as a Rpc service a class must extend procedure\Procedure class, as shown in the procedure\Login.php sample.

Authorization
-----------

The service expects an value to Authorization in the request header. The Authorization header will be validated with configs/clients.php contents. 

How to access
------------

Urls:

	http://soa.local/user/1 - show user with id = 1
	http://soa.local/users - show all users
	http://soa.local/sample - the sample.html file shows how to use the services using Javascript