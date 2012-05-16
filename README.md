
Coderockr SOA Server
=========================

The goal of this project is to enable easy access of entities using Rest and PHP scripts using RPC

This application depends on these projects:

- [Silex](http://silex.sensiolabs.org/)
- [DMS Filter](https://github.com/rdohms/DMS-Filter)
- [Doctrine](http://www.doctrine-project.org/) 
- [Symfony ClassLoader](https://github.com/symfony/ClassLoader)
- [Symfony Validator](https://github.com/symfony/Validator)
- [Symfony Console](https://github.com/symfony/Console)
- [Monolog](https://github.com/Seldaek/monolog)
- [JMS SerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle)
- [Metadata](http://github.com/schmittjoh/metadata)
- [Coderockr Image](https://raw.github.com/gist/2585353/d894a0c324bb1020a3bb97d918b343ff6c2ed29e/Coderockr_Image.php)

Instalation
----------

- Clone this project
- Execute vendors.sh to install dependencies (Linux and Mac for now)


Authorization
-----------

The service expects an value to Authorization in the request header. The Authorization header will be validated with configs/clients.php contents in client aplications. 

How to access
------------

Urls:

	http://soa.dev/user/1 - show user with id = 1
	http://soa.dev/users - show all users
	http://soa.dev/sample - the sample.html file shows how to use the services using Javascript
		