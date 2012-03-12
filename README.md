Servidor SOA da Coderockr
=========================

A idéia é facilitar o acesso a entidades usando REST e scripts PHP usando RPC


Instalação
----------


1 - rodar o vendors.sh
2 - criar a tabela de exemplo:
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(10) DEFAULT NULL,
  `password` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

3 - Criar o dominio virtual do Apache

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

4 - configurar o /etc/hosts:
127.0.0.1   soa.local

3 - acessar as urls:
http://soa.local/user/1 - mostrar o user com id 1
http://soa.local/users - mostrar todos os users
http://soa.local/sample - exemplos
