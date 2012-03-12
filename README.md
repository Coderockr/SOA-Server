Servidor SOA da Coderockr
=========================

A idéia é facilitar o acesso a entidades usando REST e scripts PHP usando RPC

Essa aplicação tem os seguintes projetos como dependência:

- [Silex](http://silex.sensiolabs.org/)
- [DMS/Filter](https://github.com/rdohms/DMS-Filter)
- [Doctrine](http://www.doctrine-project.org/) 
- [Symfony ClassLoader](https://github.com/symfony/ClassLoader)
- [Symfony Validator](https://github.com/symfony/Validator.git)

Instalação
----------

- Fazer o git clone do projeto
- Executar o vendors.sh (por enquanto só para Linux e Mac) para instalar as dependências
- Criar o dominio virtual do Apache

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

- Configurar o /etc/hosts:
    
    127.0.0.1   soa.local


Rest
----

Para que uma entidade seja disponível via RES é preciso criar uma sub classe de model\Entity. Como exemplo existe uma entidade User. Para usá-la é preciso criar a tabela abaixo. As configurações de banco de dados estão no arquivo configs/configs.php

    CREATE TABLE `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `login` varchar(10) DEFAULT NULL,
      `password` varchar(10) DEFAULT NULL,
      PRIMARY KEY (`id`)
    );

RPC
---

Para que uma classe PHP seja disponibilizada via RPC é preciso criar uma sub classe de procedure\Procedure, como a Login.php que está no diretório procedure.


Autorização
-----------

O serviço sempre vai esperar que exista um campo Authorization no header da requisição. O cabeçalho vai ser comparado com o arquivo configs/clients.php. No arquivo sample.html existem exemplos de como usar a autorização


Como acessar
------------

Para acessar os exemplos:

	http://soa.local/user/1 - mostrar o user com id 1
	http://soa.local/users - mostrar todos os users
	http://soa.local/sample - exemplos
