rm -rf vendor
mkdir vendor
cd vendor
wget http://silex.sensiolabs.org/get/silex.phar
mkdir -p DMS/Filter
git clone http://github.com/rdohms/DMS-Filter.git DMS/Filter
mkdir doctrine
git clone https://github.com/doctrine/common.git doctrine/common
git clone https://github.com/doctrine/dbal.git doctrine/dbal
git clone https://github.com/doctrine/doctrine2.git doctrine/orm
mkdir -p Symfony/Component
git clone https://github.com/symfony/ClassLoader.git Symfony/Component/Classloader
git clone https://github.com/symfony/Validator.git Symfony/Component/Validator
git clone https://github.com/symfony/Console.git Symfony/Component/Console
git clone https://github.com/Seldaek/monolog monolog
mkdir JMS
git clone git://github.com/schmittjoh/JMSSerializerBundle.git JMS/SerializerBundle
git clone http://github.com/schmittjoh/metadata.git Metadata
mkdir Coderockr
wget https://raw.github.com/gist/2585353/f33fd03de60775db38a4a229fb82a140e75e78e8/Coderockr_Image.php -O Coderockr/Image.php
