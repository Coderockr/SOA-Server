rm -rf vendor
mkdir vendor
cd vendor
# Silex
wget http://silex.sensiolabs.org/get/silex.phar
# DMS Filter
mkdir -p DMS/Filter
git clone http://github.com/rdohms/DMS-Filter.git DMS/Filter
# Doctrine ORM + DBAL + Common
mkdir doctrine
git clone https://github.com/doctrine/common.git doctrine/common
git clone https://github.com/doctrine/dbal.git doctrine/dbal
git clone https://github.com/doctrine/doctrine2.git doctrine/orm
# Symfony Components
mkdir -p Symfony/Component
git clone https://github.com/symfony/ClassLoader.git Symfony/Component/Classloader
git clone https://github.com/symfony/Validator.git Symfony/Component/Validator
git clone https://github.com/symfony/Console.git Symfony/Component/Console
# monolog
git clone https://github.com/Seldaek/monolog monolog
# JMS SerializerBundle | Serializer
mkdir JMS
git clone git://github.com/schmittjoh/JMSSerializerBundle.git JMS/SerializerBundle
# Metadata
git clone http://github.com/schmittjoh/metadata.git Metadata
mkdir Coderockr
# Coderockr Image
wget https://raw.github.com/gist/2585353/f33fd03de60775db38a4a229fb82a140e75e78e8/Coderockr_Image.php -O Coderockr/Image.php
# ImageWorkshop
git clone https://github.com/Sybio/ImageWorkshop.git